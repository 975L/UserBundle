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
use c975L\UserBundle\Service\ApiServiceInterface;
use c975L\UserBundle\Service\UserServiceInterface;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Api Controller class
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 * @copyright 2018 975L <contact@975l.com>
 */
class ApiController extends AbstractController
{
    /**
     * Stores ApiServiceInterface
     * @var ApiServiceInterface
     */
    private $apiService;
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
        ApiServiceInterface $apiService,
        ConfigServiceInterface $configService,
        EventDispatcherInterface $dispatcher,
        UserServiceInterface $userService
    )
    {
        $this->apiService = $apiService;
        $this->configService = $configService;
        $this->dispatcher = $dispatcher;
        $this->userService = $userService;
    }

//LIST
    /**
     * Lists all the users
     * @return JsonResponse
     * @throws AccessDeniedException
     *
     * @Route("/user/api/list",
     *    name="user_api_list",
     *    methods={"HEAD", "GET"})
     */
    public function listAll(Request $request, PaginatorInterface $paginator)
    {
        $this->denyAccessUnlessGranted('c975LUser-api-list', false);

        $users = $paginator->paginate(
            $this->apiService->findAll(),
            $request->query->getInt('page', 1),
            $request->query->getInt('size', 50)
        );

        $usersArray = array();
        foreach ($users->getItems() as $user) {
            $usersArray[] = $user->toArray();
        };

        return new JsonResponse($usersArray);
    }

//SEARCH
    /**
     * Searches for %{term}% in email
     * @return JsonResponse
     * @throws AccessDeniedException
     *
     * @Route("/user/api/search/{term}",
     *    name="user_api_search",
     *    requirements={"term": "^([0-9a-zA-Z]+)"},
     *    methods={"HEAD", "GET"})
     */
    public function search(Request $request, PaginatorInterface $paginator, string $term)
    {
        $this->denyAccessUnlessGranted('c975LUser-api-search', false);

        $users = $paginator->paginate(
            $this->apiService->findAllSearch($term),
            $request->query->getInt('page', 1),
            $request->query->getInt('size', 50)
        );

        $usersArray = array();
        foreach ($users->getItems() as $user) {
            $usersArray[] = $user->toArray();
        };

        return new JsonResponse($usersArray);
    }

//CREATE
    /**
     * Creates the user using the API
     * @return JsonResponse
     * @throws AccessDeniedException
     *
     * @Route("/user/api/create",
     *    name="user_api_create",
     *    methods={"HEAD", "POST"})
     */
    public function create(Request $request, ValidatorInterface $validator)
    {
        $userEntity = $this->configService->getParameter('c975LUser.entity');
        $user = new $userEntity();
        $email = $request->request->get('email');
        if (null !== $email && null === $this->userService->findUserByEmail($email)) {
            $user->setEmail($email);
            //Validates entity
            if (count($validator->validate($user)) > 0) {
                return new JsonResponse(array('error' => (string) $validator->validate($user)));
            }

            $this->denyAccessUnlessGranted('c975LUser-api-create', $user);

            //Dispatch event
            $event = new UserEvent($user, $request);
            $this->dispatcher->dispatch(UserEvent::API_USER_CREATED, $event);

            //Creates the User
            $userData = null;
            if (!$event->isPropagationStopped()) {
                $userData = $this->apiService->create($user, $request->request);
            }

            return new JsonResponse($userData);
        }

        throw new \LogicException('User already registered');
    }

//AUTHENTICATE
    /**
     * Authenticates the user using the API and returns the JWToken
     * @return JsonResponse
     * @throws AccessDeniedException
     *
     * @Route("/user/api/authenticate",
     *    name="user_api_authenticate",
     *    methods={"HEAD", "POST"})
     */
    public function authenticate(Request $request)
    {
        $user = $this->getUser();
        $this->denyAccessUnlessGranted('c975LUser-api-authenticate', $user);

        //Dispatch event
        $event = new UserEvent($user, $request);
        $this->dispatcher->dispatch(UserEvent::API_USER_AUTHENTICATE, $event);

        //Authenticates
        $authenticate = null;
        if (!$event->isPropagationStopped()) {
            $authenticate = array(
                'user' => $user->toArray(),
                'token' => $this->apiService->getToken($user, $request),
            );
        }

        return new JsonResponse($authenticate);
    }

//DISPLAY
    /**
     * Returns the json for a specific user using "/user/api/display/{identifier}"
     * @return JsonResponse
     * @throws AccessDeniedException
     * @throws NotFoundHttpException
     *
     * @Route("/user/api/display/{identifier}",
     *    name="user_api_display",
     *    requirements={"identifier": "^([0-9a-z]{32})"},
     *    methods={"HEAD", "GET"})
     */
    public function display($identifier)
    {
        $user = $this->userService->findUserByIdentifier($identifier);
        if (null !== $user) {
            $this->denyAccessUnlessGranted('c975LUser-api-display', $user);

            return new JsonResponse($user->toArray());
        }

        throw $this->createNotFoundException();
    }

//MODIFY
    /**
     * Modifies specific user using "/user/api/modify/{identifier}"
     * @return JsonResponse
     * @throws AccessDeniedException
     * @throws NotFoundHttpException
     *
     * @Route("/user/api/modify/{identifier}",
     *    name="user_api_modify",
     *    requirements={"identifier": "^([0-9a-z]{32})"},
     *    methods={"HEAD", "PUT"})
     */
    public function modify(Request $request, $identifier)
    {
        $user = $this->userService->findUserByIdentifier($identifier);
        if (null !== $user) {
            $this->denyAccessUnlessGranted('c975LUser-api-modify', $user);

            //Dispatch event
            $event = new UserEvent($user, $request);
            $this->dispatcher->dispatch(UserEvent::API_USER_MODIFY, $event);

            //Modifies the User
            if (!$event->isPropagationStopped()) {
                $this->apiService->modify($user, $request->getContent());
            }

            return new JsonResponse($user->toArray());
        }

        throw $this->createNotFoundException();
    }

//DELETE
    /**
     * Deletes specific user using "/user/api/delete/{identifier}"
     * @return JsonResponse
     * @throws AccessDeniedException
     * @throws NotFoundHttpException
     *
     * @Route("/user/api/delete/{identifier}",
     *    name="user_api_delete",
     *    requirements={"identifier": "^([0-9a-z]{32})"},
     *    methods={"HEAD", "DELETE"})
     */
    public function delete(Request $request, $identifier)
    {
        $user = $this->userService->findUserByIdentifier($identifier);
        if (null !== $user) {
            $this->denyAccessUnlessGranted('c975LUser-api-delete', $user);

            //Dispatch event
            $event = new UserEvent($user, $request);
            $this->dispatcher->dispatch(UserEvent::API_USER_DELETE, $event);

            if (!$event->isPropagationStopped()) {
                $this->apiService->delete($user);
            }

            return new JsonResponse(true);
        }

        throw $this->createNotFoundException();
    }

//ADD ROLE
    /**
     * Adds role to specific user using "/user/api/add-role/{identifier}/{role}"
     * @return JsonResponse
     * @throws AccessDeniedException
     * @throws NotFoundHttpException
     *
     * @Route("/user/api/add-role/{identifier}/{role}",
     *    name="user_api_add_role",
     *    requirements={
     *        "identifier": "^([0-9a-z]{32})",
     *        "role": "^([a-zA-Z\_]+)"
     *    },
     *    methods={"HEAD", "PUT"})
     */
    public function addRole($identifier, $role)
    {
        $user = $this->userService->findUserByIdentifier($identifier);
        if (null !== $user) {
            $this->denyAccessUnlessGranted('c975LUser-api-add-role', $this->getUser());

            $this->userService->addRole($user, $role);

            return new JsonResponse($user->toArray());
        }

        throw $this->createNotFoundException();
    }

//DELETE ROLE
    /**
     * Adds role to specific user using "/user/api/delete-role/{identifier}/{role}"
     * @return JsonResponse
     * @throws AccessDeniedException
     * @throws NotFoundHttpException
     *
     * @Route("/user/api/delete-role/{identifier}/{role}",
     *    name="user_api_delete_role",
     *    requirements={
     *        "identifier": "^([0-9a-z]{32})",
     *        "role": "^([a-zA-Z\_]+)"
     *    },
     *    methods={"HEAD", "PUT"})
     */
    public function deleteRole($identifier, $role)
    {
        $user = $this->userService->findUserByIdentifier($identifier);
        if (null !== $user) {
            $this->denyAccessUnlessGranted('c975LUser-api-delete-role', $this->getUser());

            $this->userService->deleteRole($user, $role);

            return new JsonResponse($user->toArray());
        }

        throw $this->createNotFoundException();
    }

//MODIFY ROLES
    /**
     * Modifies roles to specific user using "/user/api/modify-role/{identifier}"
     * @return JsonResponse
     * @throws AccessDeniedException
     * @throws NotFoundHttpException
     *
     * @Route("/user/api/modify-roles/{identifier}",
     *    name="user_api_modify_roles",
     *    requirements={"identifier": "^([0-9a-z]{32})"},
     *    methods={"HEAD", "PUT"})
     */
    public function modifyRoles(Request $request, $identifier)
    {
        $user = $this->userService->findUserByIdentifier($identifier);
        if (null !== $user) {
            $this->denyAccessUnlessGranted('c975LUser-api-modify-role', $this->getUser());

            $this->userService->modifyRoles($user, $request->getContent());

            return new JsonResponse($user->toArray());
        }

        throw $this->createNotFoundException();
    }

//CHANGE PASSWORD
    /**
     * Allows to change password for specific user using "/user/api/change-password"
     * @return JsonResponse
     * @throws AccessDeniedException
     *
     * @Route("/user/api/change-password",
     *    name="user_api_change_password",
     *    methods={"HEAD", "PUT"})
     */
    public function changePassword(Request $request)
    {
        $user = $this->getUser();
        $this->denyAccessUnlessGranted('c975LUser-api-change-password', $user);

        return new JsonResponse($this->apiService->changePassword($user, $request->getContent()));
    }

//RESET PASSWORD
    /**
     * Allows to reset password for specific user using "/user/api/reset-password"
     * @return JsonResponse
     * @throws AccessDeniedException
     * @throws NotFoundHttpException
     *
     * @Route("/user/api/reset-password",
     *    name="user_api_reset_password",
     *    methods={"HEAD", "PUT"})
     */
    public function resetPassword(Request $request)
    {
        $parameters = json_decode($request->getContent(), true);
        $email = array_key_exists('email', $parameters) ? $parameters['email'] : null;
        $user = $this->userService->findUserByEmail($email);
        if (null !== $user) {
            $this->denyAccessUnlessGranted('c975LUser-api-reset-password', $user);

            return new JsonResponse($this->apiService->resetPassword($user));
        }

        throw $this->createNotFoundException();
    }

//RESET PASSWORD CONFIRM
    /**
     * Confirm the reset of the password for specific user using "/user/api/reset-password-confirm"
     * @return JsonResponse
     * @throws AccessDeniedException
     * @throws NotFoundHttpException
     *
     * @Route("/user/api/reset-password-confirm/{token}",
     *    name="user_api_reset_password_confirm",
     *    requirements={"token": "^([0-9a-z]{40})"},
     *    methods={"HEAD", "PUT"})
     */
    public function resetPasswordConfirm(Request $request, $token)
    {
        $user = $this->userService->findUserByToken($token);
        if (null !== $user) {
            $this->denyAccessUnlessGranted('c975LUser-api-reset-password', $user);

            return new JsonResponse($this->apiService->resetPasswordConfirm($user, $request->getContent()));
        }

        throw $this->createNotFoundException();
    }

//EXPORT
    /**
     * Export the user's data in JSON
     * @return JsonResponse
     * @throws AccessDeniedException
     *
     * @Route("/user/api/export",
     *      name="user_api_export",
     *      methods={"GET", "HEAD"})
     */
    public function export(Request $request)
    {
        $user = $this->getUser();
        $this->denyAccessUnlessGranted('c975LUser-api-export', $user);

        //Dispatch event
        $event = new UserEvent($user, $request);
        $this->dispatcher->dispatch(UserEvent::API_USER_EXPORT, $event);

        if (!$event->isPropagationStopped()) {
            return $this->userService->export($user, 'json');
        }

        return $event->getResponse();
    }
}
