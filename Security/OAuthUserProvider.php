<?php
/*
 * (c) 2018: 975L <contact@975l.com>
 * (c) 2018: Laurent Marquet <laurent.marquet@laposte.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace c975L\UserBundle\Security;

use c975L\ConfigBundle\Service\ConfigServiceInterface;
use c975L\UserBundle\Entity\UserLight;
use c975L\UserBundle\Event\UserEvent;
use c975L\UserBundle\Service\UserServiceInterface;
use Doctrine\ORM\EntityManagerInterface;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use HWI\Bundle\OAuthBundle\Security\Core\User\OAuthAwareUserProviderInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Bridge to use hwiOAuth
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 * @copyright 2018 975L <contact@975l.com>
 */
class OAuthUserProvider implements OAuthAwareUserProviderInterface
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
     * Stores EntityManagerInterface
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * Stores current Request
     * @var Request
     */
    private $request;

    /**
     * Stores UserServiceInterface
     * @var UserServiceInterface
     */
    private $userService;

    public function __construct(
        ConfigServiceInterface $configService,
        EventDispatcherInterface $dispatcher,
        EntityManagerInterface $em,
        RequestStack $requestStack,
        UserServiceInterface $userService
    ) {
        $this->configService = $configService;
        $this->dispatcher = $dispatcher;
        $this->em = $em;
        $this->request = $requestStack->getCurrentRequest();
        $this->userService = $userService;
    }

    /**
     * Loads user
     * @return UserLight
     */
    public function loadUserByOAuthUserResponse(UserResponseInterface $response)
    {
        //Gets username
        $username = $response->getUsername();

        //Authentication suceeded
        if (null !== $username) {
            //Checks if user exists
            $user = $this->userService->findUserBySocialId($username);

            //User has been found
            if ($user instanceof UserInterface) {
                //Updates access token
                $user->setSocialToken($response->getAccessToken());

                //Persist user
                $this->em->persist($user);
                $this->em->flush();

                return $user;
            }

            //Creates user OR link account to existing one
            if (null === $user) {
                //Checks if an account already exists with the same email address
                if (null !== $response->getEmail()) {
                    $user = $this->userService->findUserByEmail($response->getEmail());
                }

                //Links account to existing one to allow sign in with both
                //Sign up with another social network, using the same email address, will replace the existing social network
                if ($user instanceof UserInterface) {
                    $user
                        ->setSocialNetwork(strtolower($response->getResourceOwner()->getName()))
                        ->setSocialId($username)
                        ->setSocialToken($response->getAccessToken())
                        ->setSocialPicture($response->getProfilePicture())
                    ;

                    //Persist user
                    $this->em->persist($user);
                    $this->em->flush();

                    return $user;
                }

                //Creates the user
                $userEntity = $this->configService->getParameter('c975LUser.entity');
                $user = new $userEntity();

                //Dispatch event USER_SIGNUP
                $event = new UserEvent($user, $this->request);
                $this->dispatcher->dispatch($event, UserEvent::USER_SIGNUP);

                //Defines data for user
                if (!$event->isPropagationStopped()) {
                    $firstname = $response->getFirstName();
                    $firstname = '' !== $firstname && null !== $firstname ? $firstname : $response->getNickname();
                    $firstname = '' !== $firstname && null !== $firstname ? $firstname : $response->getRealName();
                    $avatar = null !== $response->getEmail() ? 'https://www.gravatar.com/avatar/' . hash('md5', strtolower(trim($response->getEmail()))) . '?s=512&d=mm&r=g' : null;

                    //Allows to not have an email for first authentication, it will be requested in profile update
                    $email = null !== $response->getEmail() ? strtolower(trim($response->getEmail())) : $username;

                    $user
                        ->setIdentifier(md5($user->getEmail() . uniqid(time())))
                        ->setEmail($email)
                        ->setFirstname($firstname)
                        ->setLastname($response->getLastName())
                        ->setCreation(new \DateTime())
                        ->setAvatar($avatar)
                        ->setEnabled(true)
                        ->setPassword($username)
                        ->setSocialNetwork(strtolower($response->getResourceOwner()->getName()))
                        ->setSocialId($username)
                        ->setSocialToken($response->getAccessToken())
                        ->setSocialPicture($response->getProfilePicture())
                    ;

                    //Persist user in DB
                    $this->em->persist($user);
                    $this->em->flush();

                    //Dispatch event USER_SIGNEDUP
                    $event = new UserEvent($user, $this->request);
                    $this->dispatcher->dispatch($event, UserEvent::USER_SIGNEDUP);
                }

                return $user;
            }
        }

        return null;
    }
}
