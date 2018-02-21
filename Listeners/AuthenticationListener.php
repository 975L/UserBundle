<?php
/*
 * (c) 2018: 975l <contact@975l.com>
 * (c) 2018: Laurent Marquet <laurent.marquet@laposte.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace c975L\UserBundle\Listeners;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\AuthenticationEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Event\AuthenticationFailureEvent;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\SecurityEvents;
use c975L\UserBundle\Entity\User;

class AuthenticationListener implements EventSubscriberInterface
{
    private $em;
    private $tokenStorage;
    private $authenticationUtils;

    public function __construct(EntityManagerInterface $em, TokenStorageInterface $tokenStorage, AuthenticationUtils $authenticationUtils)
    {
        $this->em = $em;
        $this->tokenStorage = $tokenStorage;
        $this->authenticationUtils = $authenticationUtils;
    }

    public static function getSubscribedEvents()
    {
        return array(
            SecurityEvents::INTERACTIVE_LOGIN => 'onSecurityInteractiveLogin',
        );
    }

    public function onSecurityInteractiveLogin( InteractiveLoginEvent $event )
    {
        //Gets the user
        $user = $this->tokenStorage->getToken()->getUser();

        if ($user instanceof User) {
            //Writes signin time
            $user->setLatestSignin(new \DateTime());

            $this->em->persist($user);
            $this->em->flush();
        }
    }
}