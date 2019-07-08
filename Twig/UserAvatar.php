<?php
/*
 * (c) 2017: 975L <contact@975l.com>
 * (c) 2017: Laurent Marquet <laurent.marquet@laposte.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace c975L\UserBundle\Twig;

use c975L\ConfigBundle\Service\ConfigServiceInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Twig\Environment;
use Twig\TwigFunction;
use Twig\Extension\AbstractExtension;

/**
 * Twig extension to return the url to be used for user's avatar using `user_avatar([$size])`
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 * @copyright 2018 975L <contact@975l.com>
 */
class UserAvatar extends AbstractExtension
{
    /**
     * Stores ConfigServiceInterface
     * @var ConfigServiceInterface
     */
    private $configService;

    /**
     * Stores TokenStorageInterface
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    public function __construct(
        ConfigServiceInterface $configService,
        TokenStorageInterface $tokenStorage
    )
    {
        $this->configService = $configService;
        $this->tokenStorage = $tokenStorage;
    }

    public function getFunctions()
    {
        return array(
            new TwigFunction(
                'user_avatar',
                array($this, 'avatar'),
                array(
                    'needs_environment' => true,
                    'is_safe' => array('html'),
                )
            ),
        );
    }

    /**
     * Returns the avatar's url if enabled
     * @return string|null
     */
    public function avatar(Environment $environment, int $size = 128, $user = null)
    {
        //Avatar not enabled
        if (true !== $this->configService->getParameter('c975LUser.avatar')) {
            return null;
        }

        //Defines user
        if (null === $user) {
            $user = $this->tokenStorage->getToken()->getUser();
        }

        //Uses social network picture
        if ($this->configService->getParameter('c975LUser.social') && null !== $user->getSocialPicture()) {
            $avatar = $user->getSocialPicture();
        //Uses Gravatar
        } else {
            $avatar = $user->getAvatar();
        }

        //Returns avatar
        return $environment->render('@c975LUser/fragments/avatar.html.twig', array(
            'avatar' => $avatar,
            'size' => $size,
        ));
    }
}
