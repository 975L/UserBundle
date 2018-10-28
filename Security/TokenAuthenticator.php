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
use c975L\UserBundle\Service\UserServiceInterface;

/**
 * TokenAuthenticator for Api access
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 * @copyright 2018 975L <contact@975l.com>
 */
class TokenAuthenticator extends AbstractGuardAuthenticator
{
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
        ConfigServiceInterface $configService,
        UserPasswordEncoderInterface $passwordEncoder,
        UserServiceInterface $userService
    )
    {
        $this->configService = $configService;
        $this->passwordEncoder = $passwordEncoder;
        $this->userService = $userService;
    }

    public function supports(Request $request)
    {
        return $this->configService->getParameter('c975LUser.authToken') && $request->headers->has('X-AUTH-TOKEN');
    }

    public function getCredentials(Request $request)
    {
        return array(
            'identifier' => $request->headers->get('X-AUTH-TOKEN'),
        );
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        $identifier = $credentials['identifier'];

        if (null === $identifier) {
            return;
        }

        return $this->userService->findUserByIdentifier($identifier);
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