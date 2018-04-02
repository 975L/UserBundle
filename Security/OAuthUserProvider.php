<?php
/*
 * (c) 2018: 975L <contact@975l.com>
 * (c) 2018: Laurent Marquet <laurent.marquet@laposte.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace c975L\UserBundle\Security;

use Doctrine\ORM\EntityManager;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use HWI\Bundle\OAuthBundle\Security\Core\User\OAuthAwareUserProviderInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use c975L\UserBundle\Event\UserEvent;

class OAuthUserProvider implements OAuthAwareUserProviderInterface
{
    private $container;
    private $request;
    private $em;

    public function __construct(
        \Symfony\Component\DependencyInjection\ContainerInterface $container,
        \Symfony\Component\HttpFoundation\RequestStack $requestStack,
        \Doctrine\ORM\EntityManager $em
    )
    {
        $this->container = $container;
        $this->request = $requestStack->getCurrentRequest();
        $this->em = $em;
    }

    public function loadUserByOAuthUserResponse(UserResponseInterface $response)
    {
        //Gets username
        $username = $response->getUsername();

        //Authentication suceeded
        if ($username !== null) {
            //Checks if user exists
            $userService = $this->container->get(\c975L\UserBundle\Service\UserService::class);
            $user = $userService->findUserBySocialId($username);

            //User has been found
            if (is_subclass_of($user, 'c975L\UserBundle\Entity\UserAbstract')) {
                //Updates access token
                $user->setSocialToken($response->getAccessToken());

                //Persist user
                $this->em->persist($user);
                $this->em->flush();

                return $user;
            }

            //Creates user OR link account to existing one
            if ($user === null) {
                //Checks if an account already exists with the same email address
                if ($response->getEmail() !== null) {
                    $user = $userService->findUserByEmail($response->getEmail());
                }

                //Links account to existing one to allow sign in with both
                //Sign up with another social network, using the same email address, will replace the existing social network
                if (is_subclass_of($user, 'c975L\UserBundle\Entity\UserAbstract')) {
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
                $userEntity = $this->container->getParameter('c975_l_user.entity');
                $user = new $userEntity();

                //Dispatch event
                $dispatcher = $this->container->get('event_dispatcher');
                $event = new UserEvent($user, $this->request);
                $dispatcher->dispatch(UserEvent::USER_SIGNUP, $event);

                //Defines data for user
                $firstname = $response->getFirstName();
                $firstname = $firstname != '' && $firstname != null ? $firstname : $response->getNickname();
                $firstname = $firstname != '' && $firstname != null ? $firstname : $response->getRealName();
                $avatar = $response->getEmail() != null ? 'https://www.gravatar.com/avatar/' . hash('md5', strtolower(trim($response->getEmail()))) . '?s=128&d=mm&r=g' : null;

                //Allows to not have an email for first authentication, it will be requested in profile update
                $email = $response->getEmail() != null ? strtolower(trim($response->getEmail())) : $username;

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

                //Dispatch event
                $dispatcher = $this->container->get('event_dispatcher');
                $event = new UserEvent($user, $this->request);
                $dispatcher->dispatch(UserEvent::USER_SIGNEDUP, $event);

                return $user;
            }
        }
    }
}