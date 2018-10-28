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
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use c975L\ConfigBundle\Service\ConfigServiceInterface;
use c975L\UserBundle\Service\ApiServiceInterface;
use c975L\UserBundle\Service\UserServiceInterface;
use c975L\UserBundle\Event\UserEvent;

/**
 * Api Controller class
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 * @copyright 2018 975L <contact@975l.com>
 */
class ApiController extends Controller
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

//CREATE
    /**
     * Creates the user using the API
     * @return json
     * @throws AccessDeniedException
     *
     * @Route("/user/api/create",
     *      name="user_api_create", methods={"HEAD", "POST"})
     * @Method({"HEAD", "POST"})
     */
    public function create(Request $request)
    {
        $userEntity = $this->configService->getParameter('c975LUser.entity');
        $user = new $userEntity();
        $this->denyAccessUnlessGranted('c975LUser-api-create', $user);

        $userData = $this->apiService->create($user, $request->query);

        return new JsonResponse($userData);
    }

//AUTHENTICATE
    /**
     * Authenticates the user using the API
     * @return json
     * @throws AccessDeniedException
     *
     * @Route("/user/api/authenticate",
     *      name="user_api_authenticate", methods={"HEAD", "POST"})
     * @Method({"HEAD", "POST"})
     */
    public function authenticate(Request $request)
    {
        $user = $this->getUser();
        $this->denyAccessUnlessGranted('c975LUser-api-authenticate', $user);

        if (null !== $user) {
            //Dispatch event
            $event = new UserEvent($user, $request);
            $this->dispatcher->dispatch(UserEvent::USER_SIGNIN, $event);

            return new JsonResponse($user->toArray());
        }

        return new JsonResponse(false);
    }

//DISPLAY
    /**
     * Returns the json for a specific user using "/user/api/display/{identifier}"
     * @return JsonResponse
     * @throws AccessDeniedException
     *
     * @Route("/user/api/display/{identifier}",
     *    name="user_api_display",
     *    requirements={"identifier": "^([0-9a-z]{32})"},
     *    methods={"HEAD", "GET"})
     * @Method({"HEAD", "GET"})
     */
    public function display($identifier)
    {
        $user = $this->userService->findUserByIdentifier($identifier);
        $this->denyAccessUnlessGranted('c975LUser-api-display', $user);

        return new JsonResponse($user->toArray());
    }

//MODIFY
    /**
     * Modify specific child using "/child/modify/{identifier}"
     * @return JsonResponse
     * @throws AccessDeniedException
     *
     * @Route("/user/api/modify/{identifier}",
     *    name="user_api_modify",
     *    requirements={"identifier": "^([0-9a-z]{32})"},
     *    methods={"HEAD", "POST"})
     * @Method({"HEAD", "POST"})
     */
    public function modify(Request $request, $identifier)
    {
        $user = $this->userService->findUserByIdentifier($identifier);
        $this->denyAccessUnlessGranted('c975LUser-api-modify', $user);

        $this->apiService->modify($user, $request->query);

        return new JsonResponse($user->toArray());
    }

//DELETE
    /**
     * Deletes specific child using "/child/delete/{identifier}"
     * @return JsonResponse
     * @throws AccessDeniedException
     *
     * @Route("/user/api/delete/{identifier}",
     *    name="user_api_delete",
     *    requirements={"identifier": "^([0-9a-z]{32})"},
     *    methods={"HEAD", "DELETE"})
     * @Method({"HEAD", "DELETE"})
     */
    public function delete($identifier)
    {
        $user = $this->userService->findUserByIdentifier($identifier);
        $this->denyAccessUnlessGranted('c975LUser-api-delete', $user);

        $this->apiService->delete($user);

        return new JsonResponse(true);
    }
}
