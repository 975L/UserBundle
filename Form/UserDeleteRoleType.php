<?php
/*
 * (c) 2018: 975L <contact@975l.com>
 * (c) 2018: Laurent Marquet <laurent.marquet@laposte.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace c975L\UserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * UserDeleteRoleType FormType
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 * @copyright 2018 975L <contact@975l.com>
 */
class UserDeleteRoleType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $roles = $options['config']['user']->getRoles();
        $roles = array_combine($roles, $roles);
        unset($roles['ROLE_USER']);

        $builder
            ->add('user', TextType::class, array(
                'label' => 'label.user',
                'required' => true,
                'mapped' => false,
                'data' => $options['config']['user']->getEmail(),
                'attr' => array(
                    'readonly' => true,
                )))
            ->add('role', ChoiceType::class, array(
                'mapped' => false,
                'choices' => $roles,
                ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'intention' => 'UserDeleteRoleForm',
            'translation_domain' => 'user',
        ));

        $resolver->setRequired('config');
    }
}
