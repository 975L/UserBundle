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
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use c975L\UserBundle\Form\UserChangePasswordType;
use c975L\UserBundle\Form\UserResetPasswordConfirmType;
use c975L\UserBundle\Form\UserResetPasswordType;

class PasswordController extends Controller
{
    private $em;
    private $userService;

    public function __construct(
        \Doctrine\ORM\EntityManagerInterface $em,
        \c975L\UserBundle\Service\UserService $userService
    )
    {
        $this->em = $em;
        $this->userService = $userService;
    }

//CHANGE PASSWORD
    /**
     * @Route("/user/change-password",
     *      name="user_change_password")
     * @Method({"GET", "HEAD", "POST"})
     */
    public function changePassword(Request $request)
    {
        $user = $this->getUser();
        $this->denyAccessUnlessGranted('change-password', $user);

        //Defines form
        $form = $this->createForm(UserChangePasswordType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->userService->changePassword($user);

            //Redirects to display profile
            return $this->redirectToRoute('user_dashboard');
        }

        //Renders the change password form
        return $this->render('@c975LUser/forms/changePassword.html.twig', array(
            'form' => $form->createView(),
            'user' => $user,
        ));
    }

//RESET PASSWORD (REQUEST)
    /**
     * @Route("/user/reset-password",
     *      name="user_reset_password")
     * @Method({"GET", "HEAD", "POST"})
     */
    public function resetPasswordRequest(Request $request)
    {
        //Redirects signed-in user to change password
        $user = $this->getUser();
        if (is_subclass_of($user, 'c975L\UserBundle\Entity\UserAbstract')) {
            return $this->redirectToRoute('user_change_password');
        }

        //Defines form
        $form = $this->createForm(UserResetPasswordType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            //Gets user
            $user = $this->em
                ->getRepository($this->getParameter('c975_l_user.entity'))
                ->findOneByEmail(strtolower($request->request->get('user_reset_password')['email']))
            ;

            //Resets password
            $this->userService->resetPasswordRequest($user, $form->getData());

            //Renders the check email page
            return $this->redirectToRoute('user_check_email');
        }

        //Renders the reset password form
        return $this->render('@c975LUser/forms/resetPasswordRequest.html.twig', array(
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
    public function resetPasswordConfirm(Request $request, $token)
    {
        //Gets user
        $user = $this->em
            ->getRepository($this->getParameter('c975_l_user.entity'))
            ->findOneByToken($token);
        $this->denyAccessUnlessGranted('reset-password', $user);

        //Removes challenge from session incase signup has been called before
        $session = $request->getSession();
        $session->remove('challenge');
        $session->remove('challengeResult');

        //Defines form
        $form = $this->createForm(UserResetPasswordConfirmType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->userService->resetPasswordConfirm($user);

            //User is not loaded so redirects to signin
            return $this->redirectToRoute('user_signin');
        }

        //Renders the reset password form
        return $this->render('@c975LUser/forms/resetPasswordConfirm.html.twig', array(
            'form' => $form->createView(),
        ));
    }
}