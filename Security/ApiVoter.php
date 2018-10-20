<?php
/*
 * (c) 2018: 975L <contact@975l.com>
 * (c) 2018: Laurent Marquet <laurent.marquet@laposte.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace c975L\UserBundle\Security;

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
     * @var RequestStack
     */
    private $request;

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
     * Used for access to api display
     * @var string
     */
    public const API_DISPLAY = 'c975LUser-api-display';

    /**
     * Used for access to api modify
     * @var string
     */
    public const API_MODIFY = 'c975LUser-api-modify';

    /**
     * Contains all the available attributes to check with in supports()
     * @var array
     */
    private const ATTRIBUTES = array(
        self::API_CREATE,
        self::API_DELETE,
        self::API_DISPLAY,
        self::API_MODIFY,
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
            return is_subclass_of($subject, 'c975L\UserBundle\Entity\UserLightAbstract')
                && in_array($attribute, self::ATTRIBUTES)
                && $this->isApiEnabled()
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
            case self::API_CREATE:
                return $this->isSignupAllowed();
                break;
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
     * Checks if api is enabled
     * @return bool
     */
    private function isApiEnabled()
    {
        return $this->configService->getParameter('c975LUser.api');
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
