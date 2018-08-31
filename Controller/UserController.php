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
use c975L\UserBundle\Service\UserService;

class UserController extends Controller
{
//DASHBOARD
    /**
     * @Route("/user/dashboard",
     *      name="user_dashboard")
     * @Method({"GET", "HEAD"})
     */
    public function dashboard(Request $request, UserService $userService)
    {
        $user = $this->getUser();
        $this->denyAccessUnlessGranted('c975LUser-dashboard', $user);

        //Switches to user preferred language
        if (!empty($this->getParameter('c975_l_user.multilingual')) &&
            null !== $user->getLocale() &&
            $request->getLocale() != $user->getLocale()) {

            return $this->redirectToRoute('user_dashboard', array('_locale' => $user->getLocale()));
        }

        //Checks profile
        if (false === $userService->checkProfile($user)) {
            return $this->redirectToRoute('user_modify');
        }

        //Renders the dashboard
        return $this->render('@c975LUser/pages/dashboard.html.twig', array(
            'user' => $user,
            'publicProfile' => $this->getParameter('c975_l_user.publicProfile'),
        ));
    }

//CHECK EMAIL
    /**
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

//HELP
    /**
     * @Route("/user/help",
     *      name="user_help")
     * @Method({"GET", "HEAD"})
     */
    public function help()
    {
        $this->denyAccessUnlessGranted('c975LUser-help', false);

        //Renders the help
        return $this->render('@c975LUser/pages/help.html.twig');
    }
}