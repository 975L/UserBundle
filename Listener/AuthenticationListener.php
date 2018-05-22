<?php
/*
 * (c) 2018: 975L <contact@975l.com>
 * (c) 2018: Laurent Marquet <laurent.marquet@laposte.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace c975L\UserBundle\Listener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\AuthenticationEvents;
use Symfony\Component\Security\Core\Event\AuthenticationFailureEvent;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\SecurityEvents;
use c975L\UserBundle\Entity\UserAbstract;

class AuthenticationListener implements EventSubscriberInterface
{
    private $em;
    private $tokenStorage;
    private $authenticationUtils;
    private $requestStack;
    private $container;

    public function __construct(
        \Doctrine\ORM\EntityManagerInterface $em,
        \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface $tokenStorage,
        \Symfony\Component\Security\Http\Authentication\AuthenticationUtils $authenticationUtils,
        \Symfony\Component\HttpFoundation\RequestStack $requestStack,
        \Symfony\Component\DependencyInjection\ContainerInterface $container
    )
    {
        $this->em = $em;
        $this->tokenStorage = $tokenStorage;
        $this->authenticationUtils = $authenticationUtils;
        $this->request = $requestStack->getCurrentRequest();
        $this->container = $container;
    }

    public static function getSubscribedEvents()
    {
        return array(
            SecurityEvents::INTERACTIVE_LOGIN => 'onSecurityInteractiveLogin',
        );
    }

    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event)
    {
        //Gets the user
        $user = $this->tokenStorage->getToken()->getUser();

        if (is_subclass_of($user, 'c975L\UserBundle\Entity\UserAbstract')) {
            //Removes challenge from session in case a user clicked on signup, canceled and then authenticated
            $session = $this->request->getSession();
            $session->remove('challenge');
            $session->remove('challengeResult');

            //Removes sign in attempts
            $session->remove('userSigninAttempt');
            $session->remove('userSigninNewAttemptTime');

            //Writes signin time
            $user->setLatestSignin(new \DateTime());

            $this->em->persist($user);
            $this->em->flush();
        }
    }
}