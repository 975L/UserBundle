<?php
/*
 * (c) 2018: 975L <contact@975l.com>
 * (c) 2018: Laurent Marquet <laurent.marquet@laposte.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace c975L\UserBundle\Security;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use c975L\ConfigBundle\Service\ConfigServiceInterface;
use c975L\UserBundle\Service\ApiServiceInterface;
use c975L\UserBundle\Service\UserServiceInterface;

/**
 * TokenAuthenticator for Api access
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 * @copyright 2018 975L <contact@975l.com>
 */
class TokenAuthenticator extends AbstractGuardAuthenticator
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
     * Stores UserPasswordEncoderInterface
     * @var UserPasswordEncoderInterface
     */
    private $passwordEncoder;

    /**
     * Stores UserServiceInterface
     * @var UserServiceInterface
     */
    private $userService;

    public function __construct(
        ApiServiceInterface $apiService,
        ConfigServiceInterface $configService,
        UserPasswordEncoderInterface $passwordEncoder,
        UserServiceInterface $userService
    )
    {
        $this->apiService = $apiService;
        $this->configService = $configService;
        $this->passwordEncoder = $passwordEncoder;
        $this->userService = $userService;
    }

    public function supports(Request $request)
    {
        return $request->headers->has('X-AUTH-TOKEN') || $request->headers->has('Authorization');
    }

    public function getCredentials(Request $request)
    {
        $token = null !== $request->headers->get('Authorization') && 'Bearer ' === substr($request->headers->get('Authorization'), 0, 7)
            ? str_replace('Bearer ', '', $request->headers->get('Authorization'))
            : $request->headers->get('X-AUTH-TOKEN');

        return array(
            'token' => $token,
        );
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        $token = $this->apiService->validateToken($credentials['token']);

        if (null === $token) {
            return;
        }

        return $this->userService->findUserByIdentifier($token->getClaim('sub'));
    }

    public function checkCredentials($credentials, UserInterface $user)
    {
        return true;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        $data = array(
            'message' => strtr($exception->getMessageKey(), $exception->getMessageData())
        );

        return new JsonResponse($data, Response::HTTP_FORBIDDEN);
    }

    public function start(Request $request, AuthenticationException $authException = null)
    {
        $data = array(
            'message' => 'Authentication Required'
        );

        return new JsonResponse($data, Response::HTTP_UNAUTHORIZED);
    }

    public function supportsRememberMe()
    {
        return false;
    }
}