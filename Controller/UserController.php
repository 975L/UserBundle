<?php
/*
 * (c) 2018: 975L <contact@975l.com>
 * (c) 2018: Laurent Marquet <laurent.marquet@laposte.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace c975L\UserBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use c975L\ConfigBundle\Service\ConfigServiceInterface;
use c975L\UserBundle\Service\UserServiceInterface;
use c975L\UserBundle\Event\UserEvent;

/**
 * Main User Controller class
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 * @copyright 2018 975L <contact@975l.com>
 */
class UserController extends Controller
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
     * Stores UserServiceInterface
     * @var UserServiceInterface
     */
    private $userService;

    public function __construct(
        ConfigServiceInterface $configService,
        EventDispatcherInterface $dispatcher,
        UserServiceInterface $userService
    )
    {
        $this->configService = $configService;
        $this->dispatcher = $dispatcher;
        $this->userService = $userService;
    }

//SIGNIN
    /**
     * @Route("/login")
     * @Route("/signin")
     * @Method({"GET", "HEAD"})
     */
    public function signinRedirect()
    {
        //Redirects to signin
        return $this->redirectToRoute('user_signin');
    }
    /**
     * @Route("/user/signin",
     *      name="user_signin")
     * @Method({"GET", "HEAD", "POST"})
     */
    public function signin(Request $request, AuthenticationUtils $authUtils)
    {
        //Redirects to dashboard if user has already signed-in
        $user = $this->getUser();
        if (is_subclass_of($user, 'c975L\UserBundle\Entity\UserAbstract')) {
            return $this->redirectToRoute('user_dashboard');
        }

        //Dispatch event
        $event = new UserEvent($user, $request);
        $this->dispatcher->dispatch(UserEvent::USER_SIGNIN, $event);

        //Adds signin attempt
        $error = $authUtils->getLastAuthenticationError();
        extract($this->userService->addAttempt($error));

        //Returns the signin form
        return $this->render('@c975LUser/forms/signin.html.twig', array(
            'error' => $error,
            'attempt' => $attempt,
            'disabledSubmit' => $disabledSubmit,
            'site' => $this->configService->getParameter('c975LCommon.site'),
            'signup' => $this->configService->getParameter('c975LUser.signup'),
            'hwiOauth' => $this->configService->getParameter('c975LUser.hwiOauth'),
            'targetPath' => $request->query->get('_target_path'),
        ));
    }

//DASHBOARD
    /**
     * Displays the dashboard
     * @return Response
     * @throws AccessDeniedException
     *
     * @Route("/user/dashboard",
     *      name="user_dashboard")
     * @Method({"GET", "HEAD"})
     */
    public function dashboard(Request $request, UserServiceInterface $userService)
    {
        $user = $this->getUser();
        $this->denyAccessUnlessGranted('c975LUser-dashboard', $user);

        //Switches to user preferred language
        if (!empty($this->configService->getParameter('c975LUser.multilingual')) &&
            null !== $user->getLocale() &&
            $request->getLocale() !== $user->getLocale()) {

            return $this->redirectToRoute('user_dashboard', array('_locale' => $user->getLocale()));
        }

        //Checks profile
        if (!$userService->checkProfile($user)) {
            return $this->redirectToRoute('user_modify');
        }

        //Renders the dashboard
        return $this->render('@c975LUser/pages/dashboard.html.twig', array(
            'user' => $user,
            'publicProfile' => $this->configService->getParameter('c975LUser.publicProfile'),
        ));
    }

//CHECK EMAIL
    /**
     * Displays the page to inform the user to check its email's inbox
     * @return Response
     *
     * @Route("/user/check-email",
     *      name="user_check_email")
     * @Method({"GET", "HEAD"})
     */
    public function checkEmail(Request $request)
    {
        //Valid check email call
        $session = $request->getSession();
        if (null !== $session->get('checkEmailUser')) {
            $email = $session->get('checkEmailUser');
            $action = $session->get('checkEmailUserAction');

            //Removes from session
            $session->remove('checkEmailUser');
            $session->remove('checkEmailUserAction');

            //Renders the page to check email
            return $this->render('@c975LUser/pages/checkEmail.html.twig', array(
                'email' => $email,
                'action' => $action,
            ));
        }

        //Not valid check email call
        return $this->redirectToRoute('user_signin');
    }

//CONFIG
    /**
     * Displays the configuration
     * @return Response
     * @throws AccessDeniedException
     *
     * @Route("/user/config",
     *      name="user_config")
     * @Method({"GET", "HEAD", "POST"})
     */
    public function config(Request $request)
    {
        $this->denyAccessUnlessGranted('c975LUser-config', $this->getUser());

        //Defines form
        $form = $this->configService->createForm('c975l/user-bundle');
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            //Validates config
            $this->configService->setConfig($form);

            //Redirects
            return $this->redirectToRoute('user_dashboard');
        }

        //Renders the config form
        return $this->render('@c975LConfig/forms/config.html.twig', array(
            'form' => $form->createView(),
            'toolbar' => '@c975LUser',
        ));
    }

//SIGN OUT
    /**
     * Route to be defined for logout but everything is in \Listener\LogoutListener.php
     *
     * @Route("/user/signout",
     *      name="user_signout")
     * @Method({"GET", "HEAD"})
     */
    public function signout(Request $request)
    {
    }

//HELP
    /**
     * Displays the help page
     * @return Response
     * @throws AccessDeniedException
     *
     * @Route("/user/help",
     *      name="user_help")
     * @Method({"GET", "HEAD"})
     */
    public function help()
    {
        $this->denyAccessUnlessGranted('c975LUser-help', $this->getUser());

        //Renders the help
        return $this->render('@c975LUser/pages/help.html.twig');
    }
}