<?php
/*
 * (c) 2018: 975L <contact@975l.com>
 * (c) 2018: Laurent Marquet <laurent.marquet@laposte.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace c975L\UserBundle\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use c975L\UserBundle\Form\UserFormFactoryInterface;
use c975L\UserBundle\Service\UserServiceInterface;
use c975L\UserBundle\Service\Password\UserPasswordInterface;

/**
 * Password Controller class
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 * @copyright 2018 975L <contact@975l.com>
 */
class PasswordController extends Controller
{
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

    /**
     * Stores UserPasswordInterface
     * @var UserPasswordInterface
     */
    private $userPassword;

    public function __construct(
        UserFormFactoryInterface $userFormFactory,
        UserServiceInterface $userService,
        UserPasswordInterface $userPassword
    )
    {
        $this->userFormFactory = $userFormFactory;
        $this->userPassword = $userPassword;
        $this->userService = $userService;
    }

//CHANGE PASSWORD
    /**
     * Displays the form to change the password
     * @return Response
     * @throws AccessDeniedException
     *
     * @Route("/user/change-password",
     *      name="user_change_password",
     *      methods={"GET", "HEAD", "POST"})
     * @Method({"GET", "HEAD", "POST"})
     */
    public function changePassword(Request $request)
    {
        $user = $this->getUser();
        $this->denyAccessUnlessGranted('c975LUser-change-password', $user);

        //Defines form
        $form = $this->userFormFactory->create('change-password', $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->userPassword->change($user);

            //Redirects to dashboard
            return $this->redirectToRoute('user_dashboard');
        }

        //Renders the changePassword form
        return $this->render('@c975LUser/forms/changePassword.html.twig', array(
            'form' => $form->createView(),
            'user' => $user,
        ));
    }

//RESET PASSWORD (REQUEST)
    /**
     * Displays the form to reset password
     * @return Response
     *
     * @Route("/user/reset-password",
     *      name="user_reset_password",
     *      methods={"GET", "HEAD", "POST"})
     * @Method({"GET", "HEAD", "POST"})
     */
    public function resetPasswordRequest(Request $request)
    {
        //Redirects signed-in user to change password
        $user = $this->getUser();
        if ($user instanceof \Symfony\Component\Security\Core\User\AdvancedUserInterface) {
            return $this->redirectToRoute('user_change_password');
        }

        //Defines form
        $form = $this->userFormFactory->create('reset-password', null);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            //Resets password
            $this->userPassword->resetRequest($request, $form->getData());

            //Renders the check email page
            return $this->redirectToRoute('user_check_email');
        }

        //Renders the resetPassword form
        return $this->render('@c975LUser/forms/resetPasswordRequest.html.twig', array(
            'form' => $form->createView(),
        ));
    }

//RESET PASSWORD CONFIRM (FROM EMAIL LINK)
    /**
     * Displays the form to change password after reset or redirect to reset form if delay has expired
     * @return Response
     * @throws AccessDeniedException
     *
     * @Route("/user/reset-password/{token}",
     *      name="user_reset_password_confirm",
     *      requirements={"token": "^[a-zA-Z0-9]{40}$"},
     *      methods={"GET", "HEAD", "POST"})
     * @Method({"GET", "HEAD", "POST"})
     */
    public function resetPasswordConfirm(Request $request, $token)
    {
        //Gets user
        $user = $this->userService->findUserByToken($token);
        $this->denyAccessUnlessGranted('c975LUser-reset-password', $user);

        //Checks delay
        if ($this->userPassword->delayExpired($user)) {
            return $this->redirectToRoute('user_reset_password');
        }

        //Defines form
        $form = $this->userFormFactory->create('reset-password-confirm', $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->userPassword->resetConfirm($user);

            //User is not loaded so redirects to signin
            return $this->redirectToRoute('user_signin');
        }

        //Renders the resetPasswordConfirm form
        return $this->render('@c975LUser/forms/resetPasswordConfirm.html.twig', array(
            'form' => $form->createView(),
        ));
    }
}
