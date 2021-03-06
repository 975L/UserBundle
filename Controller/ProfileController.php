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
use c975L\UserBundle\Event\UserEvent;
use c975L\UserBundle\Form\UserFormFactoryInterface;
use c975L\UserBundle\Service\UserServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Profile Controller class
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 * @copyright 2018 975L <contact@975l.com>
 */
class ProfileController extends AbstractController
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
        UserFormFactoryInterface $userFormFactory,
        UserServiceInterface $userService
    ) {
        $this->configService = $configService;
        $this->dispatcher = $dispatcher;
        $this->userFormFactory = $userFormFactory;
        $this->userService = $userService;
    }

//DISPLAY
    /**
     * Displays the user's profile
     * @return Response
     * @throws AccessDeniedException
     *
     * @Route("/user/display",
     *     name="user_display",
     *     methods={"GET", "HEAD", "POST"})
     */
    public function display()
    {
        $user = $this->getUser();
        $this->denyAccessUnlessGranted('c975LUser-display', $user);

        //Checks profile
        if (!$this->userService->checkProfile($user)) {
            return $this->redirectToRoute('user_modify');
        }

        //Defines form
        $form = $this->userFormFactory->create('display', $user);

        //Renders the profile
        return $this->render(
            '@c975LUser/forms/display.html.twig',
            array(
                'form' => $form->createView(),
                'user' => $user,
            ));
    }

//MODIFY
    /**
     * Displays the form to modify the profile
     * @return Response
     * @throws AccessDeniedException
     *
     * @Route("/user/modify",
     *     name="user_modify",
     *     methods={"GET", "HEAD", "POST"})
     */
    public function modify(Request $request)
    {
        $user = $this->getUser();
        $this->denyAccessUnlessGranted('c975LUser-modify', $user);

        //Defines form
        $form = $this->userFormFactory->create('modify', $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            //Dispatch event
            $event = new UserEvent($user, $request);
            $this->dispatcher->dispatch($event, UserEvent::USER_MODIFY);

            //Modify user
            if (!$event->isPropagationStopped()) {
                $this->userService->modify($user);
            }

            //Redirects to dashboard
            return $this->redirectToRoute('user_dashboard');
        }

        //Renders the profile
        return $this->render(
            '@c975LUser/forms/modify.html.twig',
            array(
                'form' => $form->createView(),
                'user' => $user,
                'userBusiness' => $this->configService->getParameter('c975LUser.business'),
            ));
    }

//DELETE
    /**
     * Deletes the user
     * @return REsponse
     * @throws AccessDeniedException
     *
     * @Route("/user/delete",
     *     name="user_delete",
     *     methods={"GET", "HEAD", "POST"})
     */
    public function delete(Request $request)
    {
        $user = $this->getUser();
        $this->denyAccessUnlessGranted('c975LUser-delete', $user);

        //Defines form
        $form = $this->userFormFactory->create('delete', $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            //Dispatch event
            $event = new UserEvent($user, $request);
            $this->dispatcher->dispatch($event, UserEvent::USER_DELETE);

            //Deletes user
            if (!$event->isPropagationStopped()) {
                $this->userService->delete($user);
            }

            //Sign out
            return $this->redirectToRoute('user_signout');
        }

        //Renders the delete form
        return $this->render(
            '@c975LUser/forms/delete.html.twig',
            array(
                'form' => $form->createView(),
                'user' => $user,
            ));
    }

//PUBLIC PROFILE
    /**
     * Displays the public profile if enabled
     * @return Response
     * @throws AccessDeniedException
     *
     * @Route("/user/public/{identifier}",
     *     name="user_public_profile",
     *     requirements={"identifier": "^([a-z0-9]{32})$"},
     *     methods={"GET", "HEAD"})
     */
    public function publicProfile($identifier)
    {
        $user = $this->userService->findUserByIdentifier($identifier);
        $this->denyAccessUnlessGranted('c975LUser-public-profile', $user);

        //Renders the public profile
        return $this->render(
            '@c975LUser/pages/publicProfile.html.twig',
            array(
                'user' => $user,
            ));
    }

//EXPORT
    /**
     * Export the user's data in JSON or XML
     * @return Response
     * @throws AccessDeniedException
     *
     * @Route("/user/export/{format}",
     *     name="user_export",
     *     requirements={"format": "^(json|xml)$"},
     *     methods={"GET", "HEAD", "POST"})
     */
    public function export($format)
    {
        $user = $this->getUser();
        $this->denyAccessUnlessGranted('c975LUser-export', $user);

        return $this->userService->export($user, $format);
    }
}