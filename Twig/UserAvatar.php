<?php
/*
 * (c) 2017: 975L <contact@975l.com>
 * (c) 2017: Laurent Marquet <laurent.marquet@laposte.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace c975L\UserBundle\Twig;

class UserAvatar extends \Twig_Extension
{
    private $container;
    private $tokenStorage;

    public function __construct(
        \Symfony\Component\DependencyInjection\ContainerInterface $container,
        \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface $tokenStorage
        )
    {
        $this->container = $container;
        $this->tokenStorage = $tokenStorage;
    }

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction(
                'user_avatar',
                array($this, 'avatar'),
                array(
                    'needs_environment' => true,
                    'is_safe' => array('html'),
                )
            ),
        );
    }

    public function avatar(\Twig_Environment $environment, $size = 128, $user = null)
    {
        //Avatar not enabled
        if ($this->container->getParameter('c975_l_user.avatar') !== true) {
            return null;
        }

        //Defines user
        if ($user === null) {
            $user = $this->tokenStorage->getToken()->getUser();
        }

        //Uses social network picture
        if ($this->container->getParameter('c975_l_user.social') === true && $user->getSocialPicture() !== null) {
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