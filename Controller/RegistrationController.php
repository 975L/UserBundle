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
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use c975L\UserBundle\Event\UserEvent;
use c975L\UserBundle\Form\UserSignupType;

class RegistrationController extends Controller
{
    private $dispatcher;
    private $userService;

    public function __construct(
        \Symfony\Component\EventDispatcher\EventDispatcherInterface $dispatcher,
        \c975L\UserBundle\Service\UserService $userService
    )
    {
        $this->dispatcher = $dispatcher;
        $this->userService = $userService;
    }

//SIGN UP
    /**
     * @Route("/register")
     * @Route("/user/register")
     * @Route("/signup")
     * @Method({"GET", "HEAD"})
     */
    public function registerRedirect()
    {
        //Redirects to signup
        return $this->redirectToRoute('user_signup');
    }
    /**
     * @Route("/user/signup",
     *      name="user_signup")
     * @Method({"GET", "HEAD", "POST"})
     */
    public function signup(Request $request)
    {
        //Redirects if signup is disabled
        if (true !== $this->getParameter('c975_l_user.signup')) {
            return $this->redirectToRoute('user_signin');
        }

        //Redirects to dashboard if user has already signed-in
        if (is_subclass_of($this->getUser(), 'c975L\UserBundle\Entity\UserAbstract')) {
            return $this->redirectToRoute('user_dashboard');
        }

        //Defines form
        $userEntity = $this->getParameter('c975_l_user.entity');
        $user = new $userEntity();
        $userConfig = array(
            'action' => 'signup',
            'social' => $this->getParameter('c975_l_user.social'),
            'address' => $this->getParameter('c975_l_user.address'),
            'business' => $this->getParameter('c975_l_user.business'),
            'multilingual' => $this->getParameter('c975_l_user.multilingual'),
        );
        $formType = $this->getParameter('c975_l_user.signupForm') === null ? 'c975L\UserBundle\Form\UserSignupType' : $this->getParameter('c975_l_user.signupForm');
        $session = $request->getSession();
        $form = $this->createForm($formType, $user, array('session' => $session, 'userConfig' => $userConfig));
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            //Checks if challenge is ok
            if (strtoupper($session->get('challengeResult')) == strtoupper($user->getChallenge())) {
                //Dispatch event
                $event = new UserEvent($user, $request);
                $this->dispatcher->dispatch(UserEvent::USER_SIGNUP, $event);

                //Registers user
                $this->userService->signup($user);

                //Dispatch event
                $event = new UserEvent($user, $request);
                $this->dispatcher->dispatch(UserEvent::USER_SIGNEDUP, $event);

                //Renders the check email page
                $session->set('checkEmailUser', $user->getEmail());
                $session->set('checkEmailUserAction', 'signup');
                return $this->redirectToRoute('user_check_email');
            }
        }

        //Renders the signup forms
        return $this->render('@c975LUser/forms/signup.html.twig', array(
            'form' => $form->createView(),
            'touUrl' => $this->userService->getTouUrl(),
        ));
    }

//SIGN UP CONFIRM (FROM EMAIL LINK)
    /**
     * @Route("/user/register/{token}",
     *      requirements={"token": "^[a-zA-Z0-9]{40}$"})
     * @Method({"GET", "HEAD"})
     */
    public function registerConfirm(Request $request, $token)
    {
        //Redirects to signupConfirm - Kept for retro-compatibility (09/03/2018)
        return $this->redirectToRoute('user_signup_confirm');
    }
    /**
     * @Route("/user/signup/{token}",
     *      name="user_signup_confirm",
     *      requirements={"token": "^[a-zA-Z0-9]{40}$"})
     * @Method({"GET", "HEAD"})
     */
    public function signupConfirm(Request $request, $token)
    {
        //Redirects if signup is disabled
        if ($this->getParameter('c975_l_user.signup') !== true) {
            return $this->redirectToRoute('user_signin');
        }

        //Gets user
        $user = $this->getDoctrine()
            ->getManager()
            ->getRepository($this->getParameter('c975_l_user.entity'))
            ->findOneByToken($token)
        ;

        if (is_subclass_of($user, 'c975L\UserBundle\Entity\UserAbstract')) {
            //Dispatch event
            $event = new UserEvent($user, $request);
            $this->dispatcher->dispatch(UserEvent::USER_SIGNUP_CONFIRM, $event);

            //Confirms registration
            $this->userService->signupConfirm($user);
        //Token not found
        } else {
            //Creates flash
            $this->userService->createFlash('token-not-found');
        }

        //User is not loaded so redirects to signin
        return $this->redirectToRoute('user_signin');
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
            'site' => $this->getParameter('c975_l_user.site'),
            'signup' => $this->getParameter('c975_l_user.signup'),
            'hwiOauth' => $this->getParameter('c975_l_user.hwiOauth'),
            'targetPath' => $request->query->get('_target_path'),
        ));
    }

//SIGN OUT
    /**
     * @Route("/user/signout",
     *      name="user_signout")
     * @Method({"GET", "HEAD"})
     */
    public function signout(Request $request)
    {
        //This Route has to be defined for logout but everything is in \Listener\LogoutListener.php
    }
}