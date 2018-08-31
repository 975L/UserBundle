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
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use c975L\UserBundle\Event\UserEvent;
use c975L\UserBundle\Form\UserProfileType;

class ProfileController extends Controller
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

//DISPLAY
    /**
     * @Route("/user/display",
     *      name="user_display")
     * @Method({"GET", "HEAD"})
     */
    public function display()
    {
        $user = $this->getUser();
        $this->denyAccessUnlessGranted('c975LUser-display', $user);

        //Checks profile
        if ($this->userService->checkProfile($user) === false) {
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

//MODIFY
    /**
     * @Route("/user/modify",
     *      name="user_modify")
     * @Method({"GET", "HEAD", "POST"})
     */
    public function modify(Request $request, EventDispatcherInterface $dispatcher)
    {
        $user = $this->getUser();
        $this->denyAccessUnlessGranted('c975LUser-modify', $user);

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
            //Dispatch event
            $event = new UserEvent($user, $request);
            $dispatcher->dispatch(UserEvent::USER_MODIFY, $event);

            //Modify user
            $this->userService->modify($user);

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

//DELETE
    /**
     * @Route("/user/delete",
     *      name="user_delete")
     * @Method({"GET", "HEAD", "POST"})
     */
    public function delete(Request $request, EventDispatcherInterface $dispatcher)
    {
        $user = $this->getUser();
        $this->denyAccessUnlessGranted('c975LUser-delete', $user);

        //Defines the form
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
            $event = new UserEvent($user, $request);
            $dispatcher->dispatch(UserEvent::USER_DELETE, $event);

            //Deletes user
            $this->userService->delete($user);

            //Sign out
            return $this->redirectToRoute('user_signout');
        }

        //Renders the delete form
        return $this->render('@c975LUser/forms/delete.html.twig', array(
            'form' => $form->createView(),
            'user' => $user,
            ));
    }

//PUBLIC PROFILE
    /**
     * @Route("/user/public/{identifier}",
     *      name="user_public_profile",
     *      requirements={"identifier": "^([a-z0-9]{32})$"})
     * @Method({"GET", "HEAD"})
     */
    public function pulicProfile($identifier)
    {
        $user = $this->em
            ->getRepository($this->getParameter('c975_l_user.entity'))
            ->findOneByIdentifier($identifier)
            ;
        $this->denyAccessUnlessGranted('c975LUser-public-profile', $user);

        //Renders the public profile
        return $this->render('@c975LUser/pages/publicProfile.html.twig', array(
            'user' => $user,
            ));
    }

//EXPORT
    /**
     * @Route("/user/export/{format}",
     *      name="user_export",
     *      requirements={"format": "^(json|xml)$"})
     * @Method({"GET", "HEAD", "POST"})
     */
    public function export(Request $request, $format)
    {
        $user = $this->getUser();
        $this->denyAccessUnlessGranted('c975LUser-export', $user);

        return $this->userService->export($user, $format);
    }
}