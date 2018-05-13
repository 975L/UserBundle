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
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use c975L\Email\Service\EmailService;
use c975L\UserBundle\Entity\User;
use c975L\UserBundle\Event\UserEvent;
use c975L\UserBundle\Form\UserChangePasswordType;
use c975L\UserBundle\Form\UserResetPasswordConfirmType;
use c975L\UserBundle\Form\UserDeleteType;
use c975L\UserBundle\Form\UserProfileType;
use c975L\UserBundle\Form\UserSignupType;
use c975L\UserBundle\Form\UserResetPasswordType;
use c975L\UserBundle\Service\UserService;

class UserController extends Controller
{
//DASHBOARD
    /**
     * @Route("/user/dashboard",
     *      name="user_dashboard")
     * @Method({"GET", "HEAD"})
     */
    public function dashboardAction(Request $request)
    {
        //Gets user
        $user = $this->getUser();

        if (is_subclass_of($user, 'c975L\UserBundle\Entity\UserAbstract')) {
            //Switches to user preferred language
            if (!empty($this->getParameter('c975_l_user.multilingual')) &&
                $user->getLocale() !== null &&
                $request->getLocale() != $user->getLocale()) {
                return $this->redirectToRoute('user_dashboard', array('_locale' => $user->getLocale()));
            }

            //Checks profile
            $userService = $this->get(\c975L\UserBundle\Service\UserService::class);
            if ($userService->checkProfile($user) === false) {
                return $this->redirectToRoute('user_modify');
            }

            //Renders the dashboard
            return $this->render('@c975LUser/pages/dashboard.html.twig', array(
                'user' => $user,
                'publicProfile' => $this->getParameter('c975_l_user.publicProfile'),
                ));
        }

        //User not signed in
        if ($user === null) {
            return $this->redirectToRoute('user_signin');
        //Access is denied
        } else {
            throw $this->createAccessDeniedException();
        }
    }

//SIGN UP
    /**
     * @Route("/register")
     * @Route("/user/register")
     * @Route("/signup")
     * @Method({"GET", "HEAD"})
     */
    public function registerRedirectAction()
    {
        //Redirects to signup
        return $this->redirectToRoute('user_signup');
    }
    /**
     * @Route("/user/signup",
     *      name="user_signup")
     * @Method({"GET", "HEAD", "POST"})
     */
    public function signupAction(Request $request, UserPasswordEncoderInterface $passwordEncoder)
    {
        //Redirects if signup is disabled
        if ($this->getParameter('c975_l_user.signup') !== true) {
            return $this->redirectToRoute('user_signin');
        }

        //Redirects to dashboard if user has already signed-in
        if (is_subclass_of($this->getUser(), 'c975L\UserBundle\Entity\UserAbstract')) {
            return $this->redirectToRoute('user_dashboard');
        }

        //Gets the Terms of use link
        $userService = $this->get(\c975L\UserBundle\Service\UserService::class);
        $touUrl = null;
        $touUrlConfig = $this->getParameter('c975_l_user.touUrl');
        //Calculates the url if a Route is provided
        if (strpos($touUrlConfig, ',') !== false) {
            $routeData = $userService->getUrlFromRoute($touUrlConfig);
            $touUrl = $this->generateUrl($routeData['route'], $routeData['params'], UrlGeneratorInterface::ABSOLUTE_URL);
        //An url has been provided
        } elseif (strpos($touUrlConfig, 'http') !== false) {
            $touUrl = $touUrlConfig;
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
                $dispatcher = $this->get('event_dispatcher');
                $event = new UserEvent($user, $request);
                $dispatcher->dispatch(UserEvent::USER_SIGNUP, $event);

                //Adds data to user
                $user
                    ->setIdentifier(md5($user->getEmail() . uniqid(time())))
                    ->setCreation(new \DateTime())
                    ->setAvatar('https://www.gravatar.com/avatar/' . hash('md5', strtolower(trim($user->getEmail()))) . '?s=512&d=mm&r=g')
                    ->setEnabled(false)
                    ->setPassword($passwordEncoder->encodePassword($user, $user->getPlainPassword()))
                    ->setPlainPassword(null)
                    ->setToken(hash('sha1', $user->getEmail() . uniqid()))
                ;

                //Persists user in DB
                $em = $this->getDoctrine()->getManager();
                $em->persist($user);
                $em->flush();

                //Dispatch event
                $dispatcher = $this->get('event_dispatcher');
                $event = new UserEvent($user, $request);
                $dispatcher->dispatch(UserEvent::USER_SIGNEDUP, $event);

                //Gets translator
                $translator = $this->get('translator');

                //Defines email
                $body = $this->renderView('@c975LUser/emails/signup.html.twig', array(
                    'url' => $this->generateUrl('user_signup_confirm', array('token' => $user->getToken()), UrlGeneratorInterface::ABSOLUTE_URL),
                    'user' => $user,
                ));
                $emailData = array(
                    'subject' => $translator->trans('label.signup_email', array(), 'user'),
                    'sentFrom' => $this->getParameter('c975_l_email.sentFrom'),
                    'sentTo' => $user->getEmail(),
                    'replyTo' => $this->getParameter('c975_l_email.sentFrom'),
                    'body' => $body,
                    'ip' => $request->getClientIp(),
                    );

                //Sends email
                $emailService = $this->get(\c975L\EmailBundle\Service\EmailService::class);
                $emailService->send($emailData, $this->getParameter('c975_l_user.databaseEmail'));

                //Removes challenge from session
                $session->remove('challenge');
                $session->remove('challengeResult');

                //Renders the check email page
                $session->set('checkEmailUser', $user->getEmail());
                $session->set('checkEmailUserAction', 'signup');
                return $this->redirectToRoute('user_check_email');
            }
        }

        //Renders the signup forms
        return $this->render('@c975LUser/forms/signup.html.twig', array(
            'form' => $form->createView(),
            'touUrl' => $touUrl,
        ));
    }

//SIGN UP CONFIRM (FROM EMAIL LINK)
    /**
     * @Route("/user/register/{token}",
     *      requirements={"token": "^[a-zA-Z0-9]{40}$"})
     * @Method({"GET", "HEAD"})
     */
    public function registerConfirmAction(Request $request, $token)
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
    public function signupConfirmAction(Request $request, $token)
    {
        //Redirects if signup is disabled
        if ($this->getParameter('c975_l_user.signup') !== true) {
            return $this->redirectToRoute('user_signin');
        }

        //Gets the manager
        $em = $this->getDoctrine()->getManager();

        //Gets repository
        $repository = $em->getRepository($this->getParameter('c975_l_user.entity'));

        //Loads from DB
        $user = $repository->findOneByToken($token);

        if (is_subclass_of($user, 'c975L\UserBundle\Entity\UserAbstract')) {
            //Dispatch event
            $dispatcher = $this->get('event_dispatcher');
            $event = new UserEvent($user, $request);
            $dispatcher->dispatch(UserEvent::USER_SIGNUP_CONFIRM, $event);

            //Updates data
            $user
                ->setToken(null)
                ->setEnabled(true)
                ;

            //Persists data in DB
            $em->persist($user);
            $em->flush();

            //Creates flash
            $translator = $this->get('translator');
            $flash = $translator->trans('text.signup_confirmed', array(), 'user');
            $request->getSession()
                ->getFlashBag()
                ->add('success', $flash)
                ;
        //Token not found
        } else {
            //Creates flash
            $translator = $this->get('translator');
            $flash = $translator->trans('text.token_not_found', array(), 'user');
            $request->getSession()
                ->getFlashBag()
                ->add('warning', $flash)
                ;
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
    public function signinRedirectAction()
    {
        //Redirects to signin
        return $this->redirectToRoute('user_signin');
    }
    /**
     * @Route("/user/signin",
     *      name="user_signin")
     * @Method({"GET", "HEAD", "POST"})
     */
    public function signinAction(Request $request, AuthenticationUtils $authUtils)
    {
        //Gets user
        $user = $this->getUser();

        //Redirects to dashboard if user has already signed-in
        if (is_subclass_of($user, 'c975L\UserBundle\Entity\UserAbstract')) {
            return $this->redirectToRoute('user_dashboard');
        }

        //Dispatch event
        $dispatcher = $this->get('event_dispatcher');
        $event = new UserEvent($user, $request);
        $dispatcher->dispatch(UserEvent::USER_SIGNIN, $event);

        //Gets last error
        $error = $authUtils->getLastAuthenticationError();

        //Adds signin attempt
        $attempt = null;
        $disabledSubmit = '';
        if ($this->getParameter('c975_l_user.signinAttempts') > 0) {
            $delayDisable = '+15 minutes';
            $session = $request->getSession();
            $configSigninAttempts = $this->getParameter('c975_l_user.signinAttempts');

            //Adds attempt if signin didn't work
            if ($error !== null) {
                $session->set('userSigninAttempt', $session->get('userSigninAttempt') + 1);
            }

            //Defines attempt
            $sessionUserSigninAttempt = $session->get('userSigninAttempt');
            if ($sessionUserSigninAttempt > 0) {
                $attempt = $sessionUserSigninAttempt . '/' . $configSigninAttempts;
            }

            //Disables/Enables submit button
            if ($sessionUserSigninAttempt >= $configSigninAttempts) {
                //Defines time submit button will be re-enabled if max attempts (defined in config.yml) has been reached
                if ($session->get('userSigninNewAttemptTime') === null) {
                    $session->set('userSigninNewAttemptTime', new \DateTime($delayDisable));
                }

                //Disables submit button
                if (new \DateTime() < $session->get('userSigninNewAttemptTime')) {
                    $disabledSubmit = 'disabled="disabled"';
                //Enables submit button if delay is finished
                } else {
                    $session->remove('userSigninAttempt');
                    $session->remove('userSigninNewAttemptTime');
                }
            }
        }

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

//DISPLAY
    /**
     * @Route("/user/display",
     *      name="user_display")
     * @Method({"GET", "HEAD"})
     */
    public function displayAction()
    {
        //Gets user
        $user = $this->getUser();

        if (is_subclass_of($user, 'c975L\UserBundle\Entity\UserAbstract')) {
            //Checks profile
            $userService = $this->get(\c975L\UserBundle\Service\UserService::class);
            if ($userService->checkProfile($user) === false) {
                return $this->redirectToRoute('user_modify');
            }

            //Defines form
            $userConfig = array(
                'action' => 'display',
                'social' => $this->getParameter('c975_l_user.social'),
                'address' => $this->getParameter('c975_l_user.address'),
                'business' => $this->getParameter('c975_l_user.business'),
                'multilingual' => $this->getParameter('c975_l_user.multilingual'),
            );
            $formType = $this->getParameter('c975_l_user.profileForm') === null ? 'c975L\UserBundle\Form\UserProfileType' : $this->getParameter('c975_l_user.profileForm');
            $form = $this->createForm($formType, $user, array('userConfig' => $userConfig));

            //Renders the profile
            return $this->render('@c975LUser/forms/display.html.twig', array(
                'form' => $form->createView(),
                'user' => $user,
            ));
        }

        //User not signed in
        if ($user === null) {
            return $this->redirectToRoute('user_signin');
        //Access is denied
        } else {
            throw $this->createAccessDeniedException();
        }
    }

//PUBLIC PROFILE
    /**
     * @Route("/user/public/{identifier}",
     *      name="user_public_profile",
     *      requirements={
     *          "identifier": "^([a-z0-9]{32})$"
     *      })
     * @Method({"GET", "HEAD"})
     */
    public function pulicProfileAction($identifier)
    {
        //Returns the public profile if allowed
        if ($this->getParameter('c975_l_user.publicProfile') === true) {
            //Gets the manager
            $em = $this->getDoctrine()->getManager();

            //Gets repository
            $repository = $em->getRepository($this->getParameter('c975_l_user.entity'));

            //Loads from DB
            $user = $repository->findOneByIdentifier($identifier);

            //Renders the profile
            if (is_subclass_of($user, 'c975L\UserBundle\Entity\UserAbstract')) {
                return $this->render('@c975LUser/pages/publicProfile.html.twig', array(
                    'user' => $user,
                    ));
            }
        }

        //Not found
        throw $this->createNotFoundException();
    }

//MODIFY
    /**
     * @Route("/user/modify",
     *      name="user_modify")
     * @Method({"GET", "HEAD", "POST"})
     */
    public function modifyAction(Request $request)
    {
        //Gets user
        $user = $this->getUser();

        if (is_subclass_of($user, 'c975L\UserBundle\Entity\UserAbstract')) {
            //Defines form
            $userConfig = array(
                'action' => 'modify',
                'social' => $this->getParameter('c975_l_user.social'),
                'address' => $this->getParameter('c975_l_user.address'),
                'business' => $this->getParameter('c975_l_user.business'),
                'multilingual' => $this->getParameter('c975_l_user.multilingual'),
            );
            $formType = $this->getParameter('c975_l_user.profileForm') === null ? 'c975L\UserBundle\Form\UserProfileType' : $this->getParameter('c975_l_user.profileForm');
            $form = $this->createForm($formType, $user, array('userConfig' => $userConfig));
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                //Updates data
                $user->setAvatar('https://www.gravatar.com/avatar/' . hash('md5', strtolower(trim($user->getEmail()))) . '?s=512&d=mm&r=g');

                //Gets the manager
                $em = $this->getDoctrine()->getManager();

                //Persists data in DB
                $em->persist($user);
                $em->flush();

                //Creates flash
                $translator = $this->get('translator');
                $flash = $translator->trans('text.profile_modified', array(), 'user');
                $request->getSession()
                    ->getFlashBag()
                    ->add('success', $flash)
                    ;

                //Redirects to dashboard
                return $this->redirectToRoute('user_dashboard');
            }

            //Renders the profile
            return $this->render('@c975LUser/forms/modify.html.twig', array(
                'form' => $form->createView(),
                'user' => $user,
                'userConfig' => $userConfig,
            ));
        }

        //User not signed in
        if ($user === null) {
            return $this->redirectToRoute('user_signin');
        //Access is denied
        } else {
            throw $this->createAccessDeniedException();
        }
    }

//CHANGE PASSWORD
    /**
     * @Route("/user/change-password",
     *      name="user_change_password")
     * @Method({"GET", "HEAD", "POST"})
     */
    public function changePasswordAction(Request $request, UserPasswordEncoderInterface $passwordEncoder)
    {
        //Gets user
        $user = $this->getUser();

        if (is_subclass_of($user, 'c975L\UserBundle\Entity\UserAbstract')) {
            //Defines form
            $form = $this->createForm(UserChangePasswordType::class, $user);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                //Adds data to user
                $user
                    ->setPassword($passwordEncoder->encodePassword($user, $user->getPlainPassword()))
                    ->setPlainPassword(null)
                ;

                //Gets the manager
                $em = $this->getDoctrine()->getManager();

                //Persists data in DB
                $em->persist($user);
                $em->flush();

                //Creates flash
                $translator = $this->get('translator');
                $flash = $translator->trans('text.password_changed', array(), 'user');
                $request->getSession()
                    ->getFlashBag()
                    ->add('success', $flash)
                    ;

                //Redirects to display profile
                return $this->redirectToRoute('user_dashboard');
            }

            //Renders the profile
            return $this->render('@c975LUser/forms/changePassword.html.twig', array(
                'form' => $form->createView(),
                'user' => $user,
            ));
        }

        //User not signed in
        if ($user === null) {
            return $this->redirectToRoute('user_signin');
        //Access is denied
        } else {
            throw $this->createAccessDeniedException();
        }
    }

//RESET PASSWORD
    /**
     * @Route("/user/reset-password",
     *      name="user_reset_password")
     * @Method({"GET", "HEAD", "POST"})
     */
    public function resetPasswordAction(Request $request)
    {
        //Redirects signed-in user to change password
        $user = $this->getUser();
        if (is_subclass_of($user, 'c975L\UserBundle\Entity\UserAbstract')) {
            return $this->redirectToRoute('user_change_password');
        }

        //Define delay for reset (2 hours)
        $delayReset = new \DateInterval('PT2H');

        //Defines form
        $form = $this->createForm(UserResetPasswordType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            //Gets email value
            $email = strtolower($request->request->get('user_reset_password')['email']);

            //Gets the manager
            $em = $this->getDoctrine()->getManager();

            //Gets the repository
            $repository = $em->getRepository($this->getParameter('c975_l_user.entity'));

            //Gets user
            $user = $repository->findOneByEmail($email);

            //Updates data
            if (is_subclass_of($user, 'c975L\UserBundle\Entity\UserAbstract')) {
                //Request not already sent or is out of time
                if ($user->getPasswordRequest() === null || ($user->getPasswordRequest() instanceof \DateTime && $user->getPasswordRequest()->add($delayReset) < new \DateTime())) {
                    //Adds data to user
                    $user
                        ->setPasswordRequest(new \DateTime())
                        ->setToken(hash('sha1', $user->getEmail() . uniqid()))
                    ;

                    //Gets translator
                    $translator = $this->get('translator');

                    //Defines email
                    $expiryDate = new \DateTime();
                    $body = $this->renderView('@c975LUser/emails/resetPassword.html.twig', array(
                        'url' => $this->generateUrl('user_reset_password_confirm', array('token' => $user->getToken()), UrlGeneratorInterface::ABSOLUTE_URL),
                        'date' => $expiryDate->add($delayReset),
                        'user' => $user,
                    ));
                    $emailData = array(
                        'subject' => $translator->trans('label.reset_password', array(), 'user'),
                        'sentFrom' => $this->getParameter('c975_l_email.sentFrom'),
                        'sentTo' => $user->getEmail(),
                        'replyTo' => $this->getParameter('c975_l_email.sentFrom'),
                        'body' => $body,
                        'ip' => $request->getClientIp(),
                        );

                    //Sends email
                    $emailService = $this->get(\c975L\EmailBundle\Service\EmailService::class);
                    $emailService->send($emailData, $this->getParameter('c975_l_user.databaseEmail'));

                    //Gets the manager
                    $em = $this->getDoctrine()->getManager();

                    //Persists data in DB
                    $em->persist($user);
                    $em->flush();
                }
            }

            //Redirects to the page to check email
            $session = $request->getSession();
            $session->set('checkEmailUser', $form->getData()['email']);
            $session->set('checkEmailUserAction', 'resetPassword');

            //Renders the check email page
            return $this->redirectToRoute('user_check_email');
        }

        //Renders the reset password form
        return $this->render('@c975LUser/forms/resetPassword.html.twig', array(
            'form' => $form->createView(),
        ));
    }

//RESET PASSWORD CONFIRM (FROM EMAIL LINK)
    /**
     * @Route("/user/reset-password/{token}",
     *      name="user_reset_password_confirm",
     *      requirements={"token": "^[a-zA-Z0-9]{40}$"})
     * @Method({"GET", "HEAD", "POST"})
     */
    public function resetPasswordConfirmAction(Request $request, UserPasswordEncoderInterface $passwordEncoder, $token)
    {
        //Gets the manager
        $em = $this->getDoctrine()->getManager();

        //Gets repository
        $repository = $em->getRepository($this->getParameter('c975_l_user.entity'));

        //Loads from DB
        $user = $repository->findOneByToken($token);

        if (is_subclass_of($user, 'c975L\UserBundle\Entity\UserAbstract')) {
            //Removes challenge from session
            $session = $request->getSession();
            $session->remove('challenge');
            $session->remove('challengeResult');

            //Builds form
            $form = $this->createForm(UserResetPasswordConfirmType::class, $user);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                //Adds data to user
                $user
                    ->setPassword($passwordEncoder->encodePassword($user, $user->getPlainPassword()))
                    ->setPlainPassword(null)
                    ->setToken(null)
                    ->setPasswordRequest(null)
                ;

                //Persists data in DB
                $em->persist($user);
                $em->flush();

                //Creates flash
                $translator = $this->get('translator');
                $flash = $translator->trans('text.password_reset_success', array(), 'user');
                $request->getSession()
                    ->getFlashBag()
                    ->add('success', $flash)
                    ;

                //User is not loaded so redirects to signin
                return $this->redirectToRoute('user_signin');
            }

            //Renders the reset password form
            return $this->render('@c975LUser/forms/resetPasswordConfirm.html.twig', array(
                'form' => $form->createView(),
            ));
        }

        //Not found
        throw $this->createNotFoundException();
    }

//SIGN OUT
    /**
     * @Route("/user/signout",
     *      name="user_signout")
     * @Method({"GET", "HEAD"})
     */
    public function signoutAction(Request $request)
    {
        //This Route has to be defined for logout but everything is in \Listener\LogoutListener.php
    }

//DELETE
    /**
     * @Route("/user/delete",
     *      name="user_delete")
     * @Method({"GET", "HEAD", "POST"})
     */
    public function deleteAction(Request $request)
    {
        //Gets the user
        $user = $this->getUser();

        if (is_subclass_of($user, 'c975L\UserBundle\Entity\UserAbstract')) {
            //Creates the form
            $userConfig = array(
                'action' => 'delete',
                'social' => $this->getParameter('c975_l_user.social'),
                'address' => $this->getParameter('c975_l_user.address'),
                'business' => $this->getParameter('c975_l_user.business'),
                'multilingual' => $this->getParameter('c975_l_user.multilingual'),
            );
            $formType = $this->getParameter('c975_l_user.profileForm') === null ? 'c975L\UserBundle\Form\UserProfileType' : $this->getParameter('c975_l_user.profileForm');
            $form = $this->createForm($formType, $user, array('userConfig' => $userConfig));
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                //Dispatch event
                $dispatcher = $this->get('event_dispatcher');
                $event = new UserEvent($user, $request);
                $dispatcher->dispatch(UserEvent::USER_DELETE, $event);

                //Gets the manager
                $em = $this->getDoctrine()->getManager();

                //Archives user
                if ($this->getParameter('c975_l_user.archiveUser') === true) {
                    //Gets the connection
                    $conn = $em->getConnection();

                    //Calls the stored procedure
                    $query = 'CALL sp_UserArchive("' . $this->getUser()->getId() . '");';
                    $stmt = $conn->prepare($query);
                    $stmt->execute();
                    $stmt->closeCursor();
                }

                //Removes user
                $em->remove($user);

                //Flush DB
                $em->flush();

                //Gets the translator
                $translator = $this->get('translator');

                //Sends email
                $subject = $translator->trans('label.delete_account', array(), 'user');
                $body = $this->renderView('@c975LUser/emails/delete.html.twig');
                $emailData = array(
                    'subject' => $subject,
                    'sentFrom' => $this->getParameter('c975_l_email.sentFrom'),
                    'sentTo' => $user->getEmail(),
                    'sentCc' => null,
                    'replyTo' => $this->getParameter('c975_l_email.sentFrom'),
                    'body' => $body,
                    'ip' => $request->getClientIp(),
                    );
                $emailService = $this->get(\c975L\EmailBundle\Service\EmailService::class);
                $emailService->send($emailData, $this->getParameter('c975_l_user.databaseEmail'));

                //Creates flash
                $flash = $translator->trans('text.account_deleted', array(), 'user');
                $request->getSession()
                    ->getFlashBag()
                    ->add('success', $flash);

                //Sign out
                return $this->redirectToRoute('user_signout');
            }

            //Renders the delete form
            return $this->render('@c975LUser/forms/delete.html.twig', array(
                'form' => $form->createView(),
                'user' => $user,
            ));
        }

        //User not signed in
        if ($user === null) {
            return $this->redirectToRoute('user_signin');
        //Access is denied
        } else {
            throw $this->createAccessDeniedException();
        }
    }

//CHECK EMAIL
    /**
     * @Route("/user/check-email",
     *      name="user_check_email")
     * @Method({"GET", "HEAD"})
     */
    public function checkEmailAction(Request $request)
    {
        //Valid check email call
        if ($request->getSession()->get('checkEmailUser') !== null) {
            $email = $request->getSession()->get('checkEmailUser');
            $action = $request->getSession()->get('checkEmailUserAction');

            //Removes from session
            $request->getSession()->remove('checkEmailUser');
            $request->getSession()->remove('checkEmailUserAction');

            //Renders the page to check email
            return $this->render('@c975LUser/pages/checkEmail.html.twig', array(
                'email' => $email,
                'action' => $action,
            ));
        //Not valid check email call
        } else {
            return $this->redirectToRoute('user_signin');
        }
    }

//HELP
    /**
     * @Route("/user/help",
     *      name="user_help")
     * @Method({"GET", "HEAD"})
     */
    public function helpAction()
    {
        //Returns the help
        if ($this->getUser() !== null && $this->get('security.authorization_checker')->isGranted($this->getParameter('c975_l_user.roleNeeded'))) {
            return $this->render('@c975LUser/pages/help.html.twig');
        }
    }
}