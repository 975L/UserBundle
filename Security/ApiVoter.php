<?php
/*
 * (c) 2018: 975L <contact@975l.com>
 * (c) 2018: Laurent Marquet <laurent.marquet@laposte.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace c975L\UserBundle\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use c975L\ConfigBundle\Service\ConfigServiceInterface;

/**
 * Voter for Api access
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 * @copyright 2018 975L <contact@975l.com>
 */
class ApiVoter extends Voter
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
     * Stores curent Request
     * @var Request
     */
    private $request;

    /**
     * Used for access to api add-role
     * @var string
     */
    public const API_USER_ADD_ROLE = 'c975LUser-api-add-role';

    /**
     * Used for access to api authenticate
     * @var string
     */
    public const API_USER_AUTHENTICATE = 'c975LUser-api-authenticate';

    /**
     * Used for access to api change password
     * @var string
     */
    public const API_USER_CHANGE_PASSWORD = 'c975LUser-api-change-password';

    /**
     * Used for access to api create
     * @var string
     */
    public const API_USER_CREATE = 'c975LUser-api-create';

    /**
     * Used for access to api delete
     * @var string
     */
    public const API_USER_DELETE = 'c975LUser-api-delete';

    /**
     * Used for access to api delete-role
     * @var string
     */
    public const API_USER_DELETE_ROLE = 'c975LUser-api-delete-role';

    /**
     * Used for access to api display
     * @var string
     */
    public const API_USER_DISPLAY = 'c975LUser-api-display';

    /**
     * Used for access to api export
     * @var string
     */
    public const API_USER_EXPORT = 'c975LUser-api-export';

    /**
     * Used for access to api list
     * @var string
     */
    public const API_USER_LIST = 'c975LUser-api-list';

    /**
     * Used for access to api modify
     * @var string
     */
    public const API_USER_MODIFY = 'c975LUser-api-modify';

    /**
     * Used for access to api create
     * @var string
     */
    public const API_USER_MODIFY_ROLE = 'c975LUser-api-modify-role';

    /**
     * Used for access to reset password
     * @var string
     */
    public const API_USER_RESET_PASSWORD = 'c975LUser-api-reset-password';

    /**
     * Used for access to api search
     * @var string
     */
    public const API_USER_SEARCH = 'c975LUser-api-search';

    /**
     * Contains all the available attributes to check with in supports()
     * @var array
     */
    private const ATTRIBUTES = array(
        self::API_USER_ADD_ROLE,
        self::API_USER_AUTHENTICATE,
        self::API_USER_CHANGE_PASSWORD,
        self::API_USER_CREATE,
        self::API_USER_DELETE,
        self::API_USER_DELETE_ROLE,
        self::API_USER_DISPLAY,
        self::API_USER_EXPORT,
        self::API_USER_LIST,
        self::API_USER_MODIFY,
        self::API_USER_MODIFY_ROLE,
        self::API_USER_RESET_PASSWORD,
        self::API_USER_SEARCH,
    );

    public function __construct(
        ConfigServiceInterface $configService,
        AccessDecisionManagerInterface $decisionManager,
        RequestStack $requestStack
    ) {
        $this->configService = $configService;
        $this->decisionManager = $decisionManager;
        $this->request = $requestStack->getCurrentRequest();
    }

    /**
     * {@inheritdoc}
     */
    protected function supports($attribute, $subject)
    {
        if (false !== $subject) {
            return $subject instanceof UserInterface &&
                in_array($attribute, self::ATTRIBUTES) &&
                $this->isApiEnabled()
            ;
        }

        return in_array($attribute, self::ATTRIBUTES) && $this->isApiEnabled();
    }

    /**
     * {@inheritdoc}
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        //Defines access rights
        switch ($attribute) {
            case self::API_USER_ADD_ROLE:
            case self::API_USER_DELETE_ROLE:
            case self::API_USER_LIST:
            case self::API_USER_MODIFY_ROLE:
            case self::API_USER_SEARCH:
                return $this->isAllowed($token);
                break;
            case self::API_USER_CREATE:
                return $this->isSignupAllowed() && $this->isApiKeyValid($subject);
                break;
            case self::API_USER_AUTHENTICATE:
            case self::API_USER_DISPLAY:
                return $this->decisionManager->decide($token, array('ROLE_USER'));
                break;
            case self::API_USER_CHANGE_PASSWORD:
            case self::API_USER_DELETE:
            case self::API_USER_EXPORT:
            case self::API_USER_MODIFY:
                return $this->isOwner($token, $subject);
                break;
            case self::API_USER_RESET_PASSWORD:
                return true;
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

    /**
     * Checks if API is enabled
     * @return bool
     */
    private function isApiEnabled()
    {
        return $this->configService->getParameter('c975LUser.api');
    }

    /**
     * Checks if API is valid
     * @return bool
     */
    private function isApiKeyValid($subject)
    {
        return sha1($subject->getEmail() . $this->configService->getParameter('c975LUser.apiPassword')) === $this->request->request->get('apiKey');
    }

    /**
     * Checks if user is owner or has admin rights
     * @return bool
     */
    private function isOwner($token, $subject)
    {
        return $this->isAllowed($token) || (method_exists($token->getUser(), 'getId') && $subject->getId() === $token->getUser()->getId());
    }

    /**
     * Checks if signup is allowed
     * @return bool
     */
    private function isSignupAllowed()
    {
        return $this->configService->getParameter('c975LUser.signup');
    }
}
