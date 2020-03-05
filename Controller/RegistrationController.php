<?php
/*
 * (c) 2018: 975L <contact@975l.com>
 * (c) 2018: Laurent Marquet <laurent.marquet@laposte.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace c975L\UserBundle\Controller;

use c975L\ConfigBundle\Service\ConfigServiceInterface;
use c975L\ServicesBundle\Service\ServiceToolsInterface;
use c975L\UserBundle\Event\UserEvent;
use c975L\UserBundle\Form\UserFormFactoryInterface;
use c975L\UserBundle\Service\UserServiceInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Registration Controller class
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 * @copyright 2018 975L <contact@975l.com>
 */
class RegistrationController extends AbstractController
{
    /**
     * Stores ConfigServiceInterface
     * @var ConfigServiceInterface
     */
    private $configService;
    /**
     * Stores EventDispatcherInterface
     * @var EventDispatcherInterface
     */
    private $dispatcher;
    /**
     * Stores ServiceToolsInterface
     * @var ServiceToolsInterface
     */
    private $serviceTools;
    /**
     * Stores UserFormFactoryInterface
     * @var UserFormFactoryInterface
     */
    private $userFormFactory;
    /**
     * Stores UserServiceInterface
     * @var UserServiceInterface
     */
    private $userService;

    public function __construct(
        ConfigServiceInterface $configService,
        EventDispatcherInterface $dispatcher,
        ServiceToolsInterface $serviceTools,
        UserFormFactoryInterface $userFormFactory,
        UserServiceInterface $userService
    ) {
        $this->configService = $configService;
        $this->dispatcher = $dispatcher;
        $this->serviceTools = $serviceTools;
        $this->userFormFactory = $userFormFactory;
        $this->userService = $userService;
    }

//SIGN UP
    /**
     * Redirects to signup Route
     * @return Redirect
     *
     * @Route("/signup",
     *     methods={"GET", "HEAD", "POST"})
     */
    public function registerRedirect()
    {
        //Redirects to signup
        return $this->redirectToRoute('user_signup');
    }
    /**
     * Renders the signup form
     * @return Response
     * @throws AccessDeniedException
     *
     * @Route("/user/signup",
     *     name="user_signup",
     *     methods={"GET", "HEAD", "POST"})
     */
    public function signup(Request $request)
    {
        //Redirects if signup is disabled
        if (true !== $this->configService->getParameter('c975LUser.signup')) {
            return $this->redirectToRoute('user_signin');
        }

        //Redirects to dashboard if user has already signed-in
        if ($this->getUser() instanceof UserInterface) {
            return $this->redirectToRoute('user_dashboard');
        }

        $userEntity = $this->configService->getParameter('c975LUser.entity');
        $user = new $userEntity();
        $this->denyAccessUnlessGranted('c975LUser-signup', $user);

        //Defines form
        $form = $this->userFormFactory->create('signup', $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $session = $request->getSession();
            //Checks if challenge is ok
            if (strtoupper($session->get('challengeResult')) == strtoupper($user->getChallenge())) {
                //Checks if user doesn't already exist
                $email = $form->getData()->getEmail();
                if (null !== $email && null === $this->userService->findUserByEmail($email)) {
                    //Dispatch event
                    $event = new UserEvent($user, $request);
                    $this->dispatcher->dispatch($event, UserEvent::USER_SIGNUP);

                    //Registers user
                    if (!$event->isPropagationStopped()) {
                        $this->userService->signup($user);

                        //Dispatch event
                        $event = new UserEvent($user, $request);
                        $this->dispatcher->dispatch($event, UserEvent::USER_SIGNEDUP);
                    }

                    //Renders the check email page
                    $session->set('checkEmailUser', $user->getEmail());
                    $session->set('checkEmailUserAction', 'signup');
                    return $this->redirectToRoute('user_check_email');
                }

                //Creates flash
                $this->serviceTools->createFlash('user', 'text.user_already_registered', 'danger');
            }
        }

        //Renders the signup forms
        return $this->render(
            '@c975LUser/forms/signup.html.twig',
            array(
                'form' => $form->createView(),
                'touUrl' => $this->serviceTools->getUrl($this->configService->getParameter('c975LUser.touUrl')),
            ));
    }

//SIGN UP CONFIRM (FROM EMAIL LINK)
    /**
     * Confirms signup and redirects to user signin
     * @return Redirect
     * @throws AccessDeniedException
     *
     * @Route("/user/signup/{token}",
     *     name="user_signup_confirm",
     *     requirements={"token": "^[a-zA-Z0-9]{40}$"},
     *     methods={"GET", "HEAD"})
     */
    public function signupConfirm(Request $request, $token)
    {
        //Redirects if signup is disabled
        if (true !== $this->configService->getParameter('c975LUser.signup')) {
            return $this->redirectToRoute('user_signin');
        }

        //Gets user
        $user = $this->userService->findUserByToken($token);
        $this->denyAccessUnlessGranted('c975LUser-signup-confirm', $user);

        //Dispatch event
        $event = new UserEvent($user, $request);
        $this->dispatcher->dispatch($event, UserEvent::USER_SIGNUP_CONFIRM);

        //Confirms registration
        if (!$event->isPropagationStopped()) {
            $this->userService->signupConfirm($user);
        }

        //User is not loaded so redirects to signin
        return $this->redirectToRoute('user_signin');
    }
}
