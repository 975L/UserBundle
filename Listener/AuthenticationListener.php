<?php
/*
 * (c) 2018: 975L <contact@975l.com>
 * (c) 2018: Laurent Marquet <laurent.marquet@laposte.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace c975L\UserBundle\Listener;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\AuthenticationEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Event\AuthenticationFailureEvent;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\SecurityEvents;
use c975L\UserBundle\Entity\UserLightAbstract;

/**
 * Class to listen to authentication event
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 * @copyright 2018 975L <contact@975l.com>
 */
class AuthenticationListener implements EventSubscriberInterface
{
    /**
     * Stores EntityManagerInterface
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * Stores TokenStorageInterface
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * Stores current Request
     * @var Request
     */
    private $requestStack;

    public function __construct(
        EntityManagerInterface $em,
        RequestStack $requestStack,
        TokenStorageInterface $tokenStorage
    )
    {
        $this->em = $em;
        $this->request = $requestStack->getCurrentRequest();
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * Defines subscribed events
     */
    public static function getSubscribedEvents()
    {
        return array(
            SecurityEvents::INTERACTIVE_LOGIN => 'onSecurityInteractiveLogin',
        );
    }

    /**
     * Adds data to user entity and persists
     */
    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event)
    {
        $user = $this->tokenStorage->getToken()->getUser();
        if (is_subclass_of($user, 'c975L\UserBundle\Entity\UserLightAbstract')) {
            //Removes challenge from session in case a user clicked on signup, canceled and then authenticated
            $session = $this->request->getSession();
            $session->remove('challenge');
            $session->remove('challengeResult');

            //Removes sign in attempts
            $session->remove('userSigninAttempt');
            $session->remove('userSigninNewAttemptTime');

            //Writes signin time
            if (is_subclass_of($user, 'c975L\UserBundle\Entity\UserAbstract')) {
                $user->setLatestSignin(new \DateTime());

                $this->em->persist($user);
                $this->em->flush();
            }
        }
    }
}
