<?php
/*
 * (c) 2018: 975L <contact@975l.com>
 * (c) 2018: Laurent Marquet <laurent.marquet@laposte.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace c975L\UserBundle\Security;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class UserVoter extends Voter
{
    private $container;
    private $decisionManager;
    private $roleNeeded;

    public const CHANGE_PASSWORD = 'change-password';
    public const DASHBOARD = 'dashboard';
    public const DELETE = 'delete';
    public const DISPLAY = 'display';
    public const EXPORT = 'export';
    public const HELP = 'help';
    public const MODIFY = 'modify';
    public const PUBLIC_PROFILE = 'public-profile';
    public const RESET_PASSWORD = 'reset-password';

    private const ATTRIBUTES = array(
        self::CHANGE_PASSWORD,
        self::DASHBOARD,
        self::DELETE,
        self::DISPLAY,
        self::EXPORT,
        self::HELP,
        self::MODIFY,
        self::PUBLIC_PROFILE,
        self::RESET_PASSWORD,
    );

    public function __construct(
        \Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface $decisionManager,
        \Symfony\Component\DependencyInjection\ContainerInterface $container,
        string $roleNeeded
    )
    {
        $this->container = $container;
        $this->decisionManager = $decisionManager;
        $this->roleNeeded = $roleNeeded;
    }

    protected function supports($attribute, $subject)
    {
        if (false !== $subject) {
            return is_subclass_of($subject, 'c975L\UserBundle\Entity\UserAbstract') && in_array($attribute, self::ATTRIBUTES);
        }

        return in_array($attribute, self::ATTRIBUTES);
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        //Defines access rights
        switch ($attribute) {
            case self::CHANGE_PASSWORD:
            case self::DASHBOARD:
            case self::DELETE:
            case self::DISPLAY:
            case self::EXPORT:
            case self::HELP:
            case self::MODIFY:
                return $this->isOwner($token, $subject);
                break;
            case self::PUBLIC_PROFILE:
                return $this->isAllowedPublicProfile($token, $subject);
                break;
            //User class has been checked at the supports() level
            case self::RESET_PASSWORD:
                return true;
                break;
        }

        throw new \LogicException('Invalid attribute: ' . $attribute);
    }

    //Checks if public profile is allowaed by wedsite
    public function isAllowedPublicProfile()
    {
        return $this->container->getParameter('c975_l_user.publicProfile');
    }

    //Checks if user is owner or has admin rights
    private function isOwner($token, $user)
    {
        return $this->isAdmin($token) ? true : $user->getId() === $token->getUser()->getId();
    }

    //Checks if user has admin rights
    private function isAdmin($token)
    {
        return $this->decisionManager->decide($token, array($this->roleNeeded));
    }
}
