<?php
/*
 * (c) 2018: 975l <contact@975l.com>
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
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use c975L\Email\Service\EmailService;
use c975L\UserBundle\Entity\User;
use c975L\UserBundle\Form\UserChangePasswordType;
use c975L\UserBundle\Form\UserResetPasswordConfirmType;
use c975L\UserBundle\Form\UserDeleteType;
use c975L\UserBundle\Form\UserProfileType;
use c975L\UserBundle\Form\UserRegisterType;
use c975L\UserBundle\Form\UserResetPasswordType;

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

        if ($user instanceof User) {
            //Defines toolbar
            $tools  = $this->renderView('@c975LUser/tools.html.twig', array(
                'type' => 'dashboard',
            ));
            $toolbar = $this->forward('c975L\ToolbarBundle\Controller\ToolbarController::displayAction', array(
                'tools'  => $tools,
                'dashboard'  => 'user',
            ))->getContent();

            //Renders the dashboard
            return $this->render('@c975LUser/pages/dashboard.html.twig', array(
                'user' => $user,
                'data' => array('gravatar' => $this->getParameter('c975_l_user.gravatar')),
                'toolbar' => $toolbar,
                ));
        }

        //Access is denied
        throw $this->createAccessDeniedException();
    }

//REGISTER
    /**
     * @Route("/register")
     * @Method({"GET", "HEAD"})
     */
    public function registerRedirectAction()
    {
        //Redirects to register
        return $this->redirectToRoute('user_register');
    }
    /**
     * @Route("/user/register",
     *      name="user_register")
     * @Method({"GET", "HEAD", "POST"})
     */
    public function registerAction(Request $request, UserPasswordEncoderInterface $passwordEncoder)
    {
        //Redirects if registration is disabled
        if ($this->getParameter('c975_l_user.registration') !== true) {
            return $this->redirectToRoute('user_signin');
        }

        //Redirects to dashboard if user has already signed-in
        if ($this->getUser() instanceof User) {
            return $this->redirectToRoute('user_dashboard');
        }

        //Gets session
        $session = $request->getSession();

        //Defines form
        $user = new User();
        $form = $this->createForm(UserRegisterType::class, $user, array('session' => $session));
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            //Checks if challenge is ok
            if (strtoupper($session->get('challengeResult')) == strtoupper($user->getChallenge())) {
                //Adds data to user
                $user
                    ->setPassword($passwordEncoder->encodePassword($user, $user->getPlainPassword()))
                    ->setPlainPassword(null)
                    ->setCreation(new \DateTime())
                    ->setAvatar('https://www.gravatar.com/avatar/' . hash('md5', strtolower(trim($user->getEmail()))) . '?s=128&d=mm&r=g')
                    ->setToken(hash('sha1', $user->getEmail() . uniqid()))
                    ->setEnabled(false)
                ;

                //Gets translator
                $translator = $this->get('translator');

                //Defines email
                $body = $this->renderView('@c975LUser/emails/register.html.twig', array(
                    'url' => $this->generateUrl('user_register_confirm', array('token' => $user->getToken()), UrlGeneratorInterface::ABSOLUTE_URL),
                    'user' => $user,
                ));
                $emailData = array(
                    'subject' => $translator->trans('label.register_email', array(), 'user'),
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

                //Removes challenge from session
                $session->remove('challenge');
                $session->remove('challengeResult');

                //Renders the check email page
                $session->set('checkEmailUser', $user->getEmail());
                $session->set('checkEmailUserAction', 'register');
                return $this->redirectToRoute('user_check_email');
            }
        }

        //Renders the register forms
        return $this->render('@c975LUser/forms/register.html.twig', array(
            'form' => $form->createView(),
        ));
    }

//REGISTER CONFIRM (FROM EMAIL LINK)
    /**
     * @Route("/user/register/{token}",
     *      name="user_register_confirm",
     *      requirements={"token": "^[a-zA-Z0-9]{40}$"})
     * @Method({"GET", "HEAD"})
     */
    public function registerConfirmAction(Request $request, $token)
    {
        //Redirects if registration is disabled
        if ($this->getParameter('c975_l_user.registration') !== true) {
            return $this->redirectToRoute('user_signin');
        }

        //Gets the manager
        $em = $this->getDoctrine()->getManager();

        //Gets repository
        $repository = $em->getRepository('c975LUserBundle:User');

        //Loads from DB
        $user = $repository->findByToken($token);

        if ($user instanceof User) {
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
            $flash = $translator->trans('text.registration_confirmed', array(), 'user');
            $request->getSession()
                ->getFlashBag()
                ->add('success', $flash)
                ;

            //User is not loaded so redirects to signin
            return $this->redirectToRoute('user_signin');
        }

        //Not found
        throw $this->createNotFoundException();
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
        //Redirects to dashboard if user has already signed-in
        if ($this->getUser() instanceof User) {
            return $this->redirectToRoute('user_dashboard');
        }

        //Returns the signin form
        return $this->render('@c975LUser/forms/signin.html.twig', array(
            'error' => $authUtils->getLastAuthenticationError(),
            'site' => $this->getParameter('c975_l_user.site'),
            'registration' => $this->getParameter('c975_l_user.registration'),
            'hwiOauth' => $this->getParameter('c975_l_user.hwiOauth'),
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

        if ($user instanceof User) {
            //Defines form
            $form = $this->createForm(UserProfileType::class, $user);

            //Defines toolbar
            $tools  = $this->renderView('@c975LUser/tools.html.twig', array(
                'type' => 'display',
            ));
            $toolbar = $this->forward('c975L\ToolbarBundle\Controller\ToolbarController::displayAction', array(
                'tools'  => $tools,
                'dashboard'  => 'user',
            ))->getContent();

            //Renders the profile
            return $this->render('@c975LUser/forms/display.html.twig', array(
                'form' => $form->createView(),
                'user' => $user,
                'data' => array('gravatar' => $this->getParameter('c975_l_user.gravatar')),
                'toolbar' => $toolbar,
            ));
        }

        //Access is denied
        throw $this->createAccessDeniedException();
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

        if ($user instanceof User) {
            //Defines form
            $user->setAction('modify');
            $form = $this->createForm(UserProfileType::class, $user);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                //Updates data
                $user->setAvatar('https://www.gravatar.com/avatar/' . hash('md5', strtolower(trim($user->getEmail()))) . '?s=128&d=mm&r=g');

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

            //Defines toolbar
            $tools  = $this->renderView('@c975LUser/tools.html.twig', array(
                'type' => 'modify',
            ));
            $toolbar = $this->forward('c975L\ToolbarBundle\Controller\ToolbarController::displayAction', array(
                'tools'  => $tools,
                'dashboard'  => 'user',
            ))->getContent();

            //Renders the profile
            return $this->render('@c975LUser/forms/modify.html.twig', array(
                'form' => $form->createView(),
                'user' => $user,
                'data' => array('gravatar' => $this->getParameter('c975_l_user.gravatar')),
                'toolbar' => $toolbar,
            ));
        }

        //Access is denied
        throw $this->createAccessDeniedException();
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

        if ($user instanceof User) {
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

            //Defines toolbar
            $tools  = $this->renderView('@c975LUser/tools.html.twig', array(
                'type' => 'changePassword',
            ));
            $toolbar = $this->forward('c975L\ToolbarBundle\Controller\ToolbarController::displayAction', array(
                'tools'  => $tools,
                'dashboard'  => 'user',
            ))->getContent();

            //Renders the profile
            return $this->render('@c975LUser/forms/changePassword.html.twig', array(
                'form' => $form->createView(),
                'user' => $user,
                'data' => array('gravatar' => $this->getParameter('c975_l_user.gravatar')),
                'toolbar' => $toolbar,
            ));
        }

        //Access is denied
        throw $this->createAccessDeniedException();
    }

//RESET PASSWORD
    /**
     * @Route("/user/reset-password",
     *      name="user_reset_password")
     * @Method({"GET", "HEAD", "POST"})
     */
    public function resetPasswordAction(Request $request)
    {
        //Define delay for reset (2 hours)
        $delayReset = new \DateInterval('PT2H');

        //Redirects signed-in user to change password
        $user = $this->getUser();
        if ($user instanceof User) {
            return $this->redirectToRoute('user_change_password');
        }

        //Defines form
        $form = $this->createForm(UserResetPasswordType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            //Gets email value
            $email = $request->request->get('user_reset_password')['email'];

            //Gets the manager
            $em = $this->getDoctrine()->getManager();

            //Gets the repository
            $repository = $em->getRepository('c975LUserBundle:User');

            //Gets user
            $user = $repository->findByEmail($email);

            //Updates data
            if ($user instanceof User) {
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

                //Redirects to the page to check email
                $session = $request->getSession();
                $session->set('checkEmailUser', $user->getEmail());
                $session->set('checkEmailUserAction', 'resetPassword');

                //Renders the check email page
                return $this->redirectToRoute('user_check_email');
            }

            //Not found
            throw $this->createNotFoundException();
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
        $repository = $em->getRepository('c975LUserBundle:User');

        //Loads from DB
        $user = $repository->findByToken($token);

        if ($user instanceof User) {
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
        //This Route has to be defined for logout but everything is in \Listeners\LogoutListenr.php
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

        if ($user instanceof User) {
            //Creates the form
            $user->setAction('delete');
            $form = $this->createForm(UserProfileType::class, $user);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                //Gets the translator
                $translator = $this->get('translator');

                //Calls user's defined method if overriden
                $this->deleteAccountUserDefinedMethod();

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

                //Archives user
                if ($this->getParameter('c975_l_user.archiveUser') === true) {
                    $this->archiveUserDefinedMethod();
                }

                //Gets the manager
                $em = $this->getDoctrine()->getManager();

                //Removes user
                $em->remove($user);

                //Flush DB
                $em->flush();

                //Creates flash
                $flash = $translator->trans('text.account_deleted', array(), 'user');
                $request->getSession()
                    ->getFlashBag()
                    ->add('success', $flash);

                //Sign out
                return $this->redirectToRoute('user_signout');
            }

            //Defines toolbar
            $tools  = $this->renderView('@c975LUser/tools.html.twig', array(
                'type' => 'delete',
            ));
            $toolbar = $this->forward('c975L\ToolbarBundle\Controller\ToolbarController::displayAction', array(
                'tools'  => $tools,
                'dashboard'  => 'user',
            ))->getContent();

            //Renders the delete form
            return $this->render('@c975LUser/forms/delete.html.twig', array(
                'form' => $form->createView(),
                'user' => $user,
                'data' => array('gravatar' => $this->getParameter('c975_l_user.gravatar')),
                'toolbar' => $toolbar,
            ));
        }

        //Access is denied
        throw $this->createAccessDeniedException();
    }

//USER DELETE ACCOUNT METHOD
    /*
     * Override this method in your Controller to add you own actions to deleteAccountAction
     */
    public function deleteAccountUserDefinedMethod()
    {
    }

//ARCHIVE USER
    /*
     * Override this method in your Controller to add you own actions to archiveUser
     */
    public function archiveUserDefinedMethod()
    {
        //Gets the connection
        $conn = $this->getDoctrine()->getManager()->getConnection();

        //Calls the stored procedure
        $query = 'CALL sp_UserArchive("' . $this->getUser()->getId() . '");';
        $stmt = $conn->prepare($query);
        $stmt->execute();
        $stmt->closeCursor();
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
}