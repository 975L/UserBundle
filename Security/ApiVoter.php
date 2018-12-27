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
    public const API_ADD_ROLE = 'c975LUser-api-add-role';

    /**
     * Used for access to api authenticate
     * @var string
     */
    public const API_AUTHENTICATE = 'c975LUser-api-authenticate';

    /**
     * Used for access to api create
     * @var string
     */
    public const API_CREATE = 'c975LUser-api-create';

    /**
     * Used for access to api delete
     * @var string
     */
    public const API_DELETE = 'c975LUser-api-delete';

    /**
     * Used for access to api delete-role
     * @var string
     */
    public const API_DELETE_ROLE = 'c975LUser-api-delete-role';

    /**
     * Used for access to api display
     * @var string
     */
    public const API_DISPLAY = 'c975LUser-api-display';

    /**
     * Used for access to api list
     * @var string
     */
    public const API_LIST = 'c975LUser-api-list';

    /**
     * Used for access to api modify
     * @var string
     */
    public const API_MODIFY = 'c975LUser-api-modify';

    /**
     * Used for access to api search
     * @var string
     */
    public const API_SEARCH = 'c975LUser-api-search';

    /**
     * Contains all the available attributes to check with in supports()
     * @var array
     */
    private const ATTRIBUTES = array(
        self::API_ADD_ROLE,
        self::API_AUTHENTICATE,
        self::API_CREATE,
        self::API_DELETE,
        self::API_DELETE_ROLE,
        self::API_DISPLAY,
        self::API_LIST,
        self::API_MODIFY,
        self::API_SEARCH,
    );

    public function __construct(
        ConfigServiceInterface $configService,
        AccessDecisionManagerInterface $decisionManager,
        RequestStack $requestStack
    )
    {
        $this->configService = $configService;
        $this->decisionManager = $decisionManager;
        $this->request = $requestStack->getCurrentRequest();
    }

    /**
     * Checks if attribute and subject are supported
     * @return bool
     */
    protected function supports($attribute, $subject)
    {
        if (false !== $subject) {
            return $subject instanceof \Symfony\Component\Security\Core\User\AdvancedUserInterface &&
                in_array($attribute, self::ATTRIBUTES) &&
                $this->isApiEnabled()
            ;
        }

        return in_array($attribute, self::ATTRIBUTES) && $this->isApiEnabled();
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
            case self::API_ADD_ROLE:
            case self::API_DELETE_ROLE:
            case self::API_LIST:
            case self::API_SEARCH:
                return $this->isAllowed($token);
                break;
            case self::API_CREATE:
                return $this->isSignupAllowed() && $this->isApiKeyValid($subject);
                break;
            case self::API_AUTHENTICATE:
            case self::API_DISPLAY:
                return $this->decisionManager->decide($token, array('ROLE_USER'));
                break;
            case self::API_DELETE:
            case self::API_MODIFY:
                return $this->isOwner($token, $subject);
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
