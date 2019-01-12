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
use Symfony\Component\Security\Core\User\AdvancedUserInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use c975L\ConfigBundle\Service\ConfigServiceInterface;

/**
 * Voter for User access
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 * @copyright 2018 975L <contact@975l.com>
 */
class ManageVoter extends Voter
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
     * Used for access to manage
     * @var string
     */
    public const MANAGE = 'c975LUser-manage';

    /**
     * Used for access to manage-add-role
     * @var string
     */
    public const MANAGE_ADD_ROLE = 'c975LUser-manage-add-role';

    /**
     * Used for access to manage-config
     * @var string
     */
    public const MANAGE_CONFIG = 'c975LUser-manage-config';

    /**
     * Used for access to manage-display
     * @var string
     */
    public const MANAGE_DISPLAY = 'c975LUser-manage-display';

    /**
     * Used for access to manage-delete
     * @var string
     */
    public const MANAGE_DELETE = 'c975LUser-manage-delete';

    /**
     * Used for access to manage-delete-role
     * @var string
     */
    public const MANAGE_DELETE_ROLE = 'c975LUser-manage-delete-role';

    /**
     * Used for access to manage-modify
     * @var string
     */
    public const MANAGE_MODIFY = 'c975LUser-manage-modify';

    /**
     * Contains all the available attributes to check with in supports()
     * @var array
     */
    private const ATTRIBUTES = array(
        self::MANAGE,
        self::MANAGE_ADD_ROLE,
        self::MANAGE_CONFIG,
        self::MANAGE_DISPLAY,
        self::MANAGE_DELETE,
        self::MANAGE_DELETE_ROLE,
        self::MANAGE_MODIFY,
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
            return ($subject instanceof AdvancedUserInterface) && in_array($attribute, self::ATTRIBUTES);
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
        //If access is restricted to API only
        if ($this->configService->getParameter('c975LUser.apiOnly')) {
            return false;
        }

        //Defines access rights
        switch ($attribute) {
            case self::MANAGE:
            case self::MANAGE_ADD_ROLE:
            case self::MANAGE_CONFIG:
            case self::MANAGE_DISPLAY:
            case self::MANAGE_DELETE:
            case self::MANAGE_DELETE_ROLE:
            case self::MANAGE_MODIFY:
                return $this->isAllowed($token);
                break;
        }

        throw new \LogicException('Invalid attribute: ' . $attribute);
    }

    /**
     * Checks if user has sufficient rights
     * @return bool
     */
    private function isAllowed($token)
    {
        return $this->decisionManager->decide($token, array($this->configService->getParameter('c975LUser.roleNeeded', 'c975l/user-bundle')));
    }
}
