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
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use c975L\ConfigBundle\Service\ConfigServiceInterface;

/**
 * Voter for User access
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 * @copyright 2018 975L <contact@975l.com>
 */
class UserVoter extends Voter
{
    /**
     * Stores ConfigServiceInterface
     * @var ConfigServiceInterface
     */
    private $configService;

    /**
     * Stores AccessDecisionManagerInterface
     * @var AccessDecisionManagerInterface
     */
    private $decisionManager;

    /**
     * Used for access to change-password
     * @var string
     */
    public const CHANGE_PASSWORD = 'c975LUser-change-password';

    /**
     * Used for access to dashboard
     * @var string
     */
    public const DASHBOARD = 'c975LUser-dashboard';

    /**
     * Used for access to delete
     * @var string
     */
    public const DELETE = 'c975LUser-delete';

    /**
     * Used for access to display
     * @var string
     */
    public const DISPLAY = 'c975LUser-display';

    /**
     * Used for access to export
     * @var string
     */
    public const EXPORT = 'c975LUser-export';

    /**
     * Used for access to help
     * @var string
     */
    public const HELP = 'c975LUser-help';

    /**
     * Used for access to modify
     * @var string
     */
    public const MODIFY = 'c975LUser-modify';

    /**
     * Used for access to public-profile
     * @var string
     */
    public const PUBLIC_PROFILE = 'c975LUser-public-profile';

    /**
     * Used for access to reset-password
     * @var string
     */
    public const RESET_PASSWORD = 'c975LUser-reset-password';

    /**
     * Contains all the available attributes to check with in supports()
     * @var array
     */
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
        ConfigServiceInterface $configService,
        AccessDecisionManagerInterface $decisionManager
    )
    {
        $this->configService = $configService;
        $this->decisionManager = $decisionManager;
    }

    /**
     * Checks if attribute and subject are supported
     * @return bool
     */
    protected function supports($attribute, $subject)
    {
        if (false !== $subject) {
            return is_subclass_of($subject, 'c975L\UserBundle\Entity\UserAbstract') && in_array($attribute, self::ATTRIBUTES);
        }

        return in_array($attribute, self::ATTRIBUTES);
    }

    /**
     * Votes if access is granted
     * @return bool
     * @throws \LogicException
     */
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

    /**
     * Checks if public profile is allowed by website
     * @return bool
     */
    public function isAllowedPublicProfile()
    {
        return $this->configService->getParameter('c975LUser.publicProfile');
    }

    /**
     * Checks if user is owner or has admin rights
     * @return bool
     */
    private function isOwner($token, $user)
    {
        return $this->isAdmin($token) && $user->getId() === $token->getUser()->getId();
    }

    /**
     * Checks if user has admin rights
     * @return bool
     */
    private function isAdmin($token)
    {
        return $this->decisionManager->decide($token, array($this->configService->getParameter('c975LUser.roleNeeded', 'c975l/user-bundle')));
    }
}
