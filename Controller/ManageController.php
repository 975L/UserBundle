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
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\DisabledException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Exception\NotFoundHttpException;

/**
 * Manage User Controller class
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 * @copyright 2018 975L <contact@975l.com>
 */
class ManageController extends AbstractController
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
    )
    {
        $this->configService = $configService;
        $this->dispatcher = $dispatcher;
        $this->userFormFactory = $userFormFactory;
        $this->userService = $userService;
    }

//MANAGE
    /**
     * Manage the users
     * @return Response
     * @throws AccessDeniedException
     *
     * @Route("/user/manage",
     *      name="user_manage",
     *      methods={"GET", "HEAD"})
     */
    public function manage(Request $request, PaginatorInterface $paginator)
    {
        $user = $this->getUser();
        $this->denyAccessUnlessGranted('c975LUser-manage', $user);

        //Renders the list of users
        $users = $paginator->paginate(
            $this->userService->getUsersAll(),
            $request->query->getInt('p', 1),
            $request->query->getInt('s', 50)
        );
        return $this->render('@c975LUser/pages/users.html.twig', array(
            'currentUser' => $user,
            'users' => $users,
        ));
    }

//DISPLAY
    /**
     * Adds a role to the user
     * @return Response
     * @throws AccessDeniedException
     * @throws NotFoundHttpException
     *
     * @Route("/user/manage/display/{identifier}",
     *      name="user_manage_display",
     *      requirements={"identifier": "^([a-z0-9]{32})$"},
     *      methods={"GET", "HEAD"})
     */
    public function display(Request $request, $identifier)
    {
        $user = $this->getUser();
        $this->denyAccessUnlessGranted('c975LUser-manage-display', $user);

        //Gets managed user
        $managedUser = $this->userService->findUserByIdentifier($identifier);
        if (null === $managedUser) {
            throw $this->createNotFoundException();
        }

        //Defines form
        $form = $this->userFormFactory->create('display', $managedUser);

        //Renders the display form
        return $this->render('@c975LUser/forms/display.html.twig', array(
            'form' => $form->createView(),
            'user' => $managedUser,
        ));
    }

//MODIFY
    /**
     * Adds a role to the user
     * @return Response
     * @throws AccessDeniedException
     * @throws NotFoundHttpException
     *
     * @Route("/user/manage/modify/{identifier}",
     *      name="user_manage_modify",
     *      requirements={"identifier": "^([a-z0-9]{32})$"},
     *      methods={"GET", "HEAD", "POST"})
     */
    public function modify(Request $request, $identifier)
    {
        $user = $this->getUser();
        $this->denyAccessUnlessGranted('c975LUser-manage-modify', $user);

        //Gets managed user
        $managedUser = $this->userService->findUserByIdentifier($identifier);
        if (null === $managedUser) {
            throw $this->createNotFoundException();
        }

        //Defines form
        $form = $this->userFormFactory->create('modify', $managedUser);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            //Modify user
            $this->userService->modify($user);

            //Redirects to manage users
            return $this->redirectToRoute('user_manage');
        }

        //Renders the modify form
        return $this->render('@c975LUser/forms/modify.html.twig', array(
            'form' => $form->createView(),
            'user' => $managedUser,
            'userBusiness' => $this->configService->getParameter('c975LUser.business'),
        ));
    }

//ADD ROLE
    /**
     * Adds a role to the user
     * @return Response
     * @throws AccessDeniedException
     * @throws NotFoundHttpException
     *
     * @Route("/user/manage/add-role/{identifier}",
     *      name="user_manage_add_role",
     *      requirements={"identifier": "^([a-z0-9]{32})$"},
     *      methods={"GET", "HEAD", "POST"})
     */
    public function addRole(Request $request, $identifier)
    {
        $user = $this->getUser();
        $this->denyAccessUnlessGranted('c975LUser-manage-add-role', $user);

        //Gets managed user
        $managedUser = $this->userService->findUserByIdentifier($identifier);
        if (null === $managedUser) {
            throw $this->createNotFoundException();
        }

        //Defines form
        $form = $this->userFormFactory->create('add-role', $managedUser);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->userService->addRole($managedUser, $form['role']->getData());

            //Redirects to manage users
            return $this->redirectToRoute('user_manage');
        }

        //Renders the addRole form
        return $this->render('@c975LUser/forms/addRole.html.twig', array(
            'form' => $form->createView(),
            'user' => $managedUser,
        ));
    }

//DELETE ROLE
    /**
     * Displays the dashboard
     * @return Response
     * @throws AccessDeniedException
     * @throws NotFoundHttpException
     *
     * @Route("/user/manage/delete-role/{identifier}",
     *      name="user_manage_delete_role",
     *      requirements={"identifier": "^([a-z0-9]{32})$"},
     *      methods={"GET", "HEAD", "POST"})
     */
    public function deleteRole(Request $request, $identifier)
    {
        $user = $this->getUser();
        $this->denyAccessUnlessGranted('c975LUser-manage-delete-role', $user);

        //Gets managed user
        $managedUser = $this->userService->findUserByIdentifier($identifier);
        if (null === $managedUser) {
            throw $this->createNotFoundException();
        }

        //Defines form
        $form = $this->userFormFactory->create('delete-role', $managedUser);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->userService->deleteRole($managedUser, $form['role']->getData());

            //Redirects to manage users
            return $this->redirectToRoute('user_manage');
        }

        //Renders the deleteRole form
        return $this->render('@c975LUser/forms/deleteRole.html.twig', array(
            'form' => $form->createView(),
            'user' => $managedUser,
        ));
    }

//DELETE
    /**
     * Adds a role to the user
     * @return Response
     * @throws AccessDeniedException
     * @throws NotFoundHttpException
     *
     * @Route("/user/manage/delete/{identifier}",
     *      name="user_manage_delete",
     *      requirements={"identifier": "^([a-z0-9]{32})$"},
     *      methods={"GET", "HEAD", "POST"})
     */
    public function delete(Request $request, $identifier)
    {
        $user = $this->getUser();
        $this->denyAccessUnlessGranted('c975LUser-manage-delete', $user);

        //Gets managed user
        $managedUser = $this->userService->findUserByIdentifier($identifier);
        if (null === $managedUser) {
            throw $this->createNotFoundException();
        }

        //Defines form
        $form = $this->userFormFactory->create('delete', $managedUser);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            //Dispatch event
            $event = new UserEvent($managedUser, $request);
            $this->dispatcher->dispatch($event, UserEvent::USER_DELETE);

            //Deletes managedUser
            if (!$event->isPropagationStopped()) {
                $this->userService->delete($managedUser);
            }

            //Redirects to manage users
            return $this->redirectToRoute('user_manage');
        }

        //Renders the delete form
        return $this->render('@c975LUser/forms/delete.html.twig', array(
            'form' => $form->createView(),
            'user' => $managedUser,
        ));
    }
}
