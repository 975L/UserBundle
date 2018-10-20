<?php
/*
 * (c) 2018: 975L <contact@975l.com>
 * (c) 2018: Laurent Marquet <laurent.marquet@laposte.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace c975L\UserBundle\Form;

use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use c975L\ConfigBundle\Service\ConfigServiceInterface;
use c975L\UserBundle\Form\UserFormFactoryInterface;
use c975L\UserBundle\Form\UserChangePasswordType;
use c975L\UserBundle\Form\UserResetPasswordConfirmType;
use c975L\UserBundle\Form\UserResetPasswordType;
use c975L\UserBundle\Form\UserProfileType;
use c975L\UserBundle\Form\UserSignupType;

/**
 * UserFormFactory class
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 * @copyright 2018 975L <contact@975l.com>
 */
class UserFormFactory implements UserFormFactoryInterface
{
    /**
     * Stores ConfigServiceInterface
     * @var ConfigServiceInterface
     */
    private $configService;

    /**
     * Stores FormFactoryInterface
     * @var FormFactoryInterface
     */
    private $formFactory;

    /**
     * Stores curent Request
     * @var RequestStack
     */
    private $request;

    public function __construct(
        ConfigServiceInterface $configService,
        FormFactoryInterface $formFactory,
        RequestStack $requestStack
    )
    {
        $this->configService = $configService;
        $this->formFactory = $formFactory;
        $this->request = $requestStack->getCurrentRequest();
    }

    /**
     * {@inheritdoc}
     */
    public function create(string $name, $user)
    {
        $config = array(
            'social' => $this->configService->getParameter('c975LUser.social'),
            'address' => $this->configService->getParameter('c975LUser.address'),
            'business' => $this->configService->getParameter('c975LUser.business'),
            'multilingual' => $this->configService->getParameter('c975LUser.multilingual'),
        );

        switch ($name) {
            case 'change-password':
                $form = UserChangePasswordType::class;
                $config = array();
                break;
            case 'delete':
            case 'display':
            case 'modify':
                $form = null !== $this->configService->getParameter('c975LUser.profileForm') ? $this->configService->getParameter('c975LUser.profileForm') : UserProfileType::class;
                $config['action'] = $name;
                break;
            case 'reset-password':
                $form = UserResetPasswordType::class;
                $config = array();
                break;
            case 'reset-password-confirm':
                $form = UserResetPasswordConfirmType::class;
                break;
            case 'signup':
                $form = null !== $this->configService->getParameter('c975LUser.signupForm') ? $this->configService->getParameter('c975LUser.signupForm') : UserSignupType::class;
                $config['action'] = $name;
                $config['session'] = $this->request->getSession();
                break;
            default:
                break;
        }

        return $this->formFactory->create($form, $user, array('config' => $config));
    }
}
