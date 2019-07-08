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
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * UserAddRoleType FormType
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 * @copyright 2018 975L <contact@975l.com>
 */
class UserAddRoleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('user', TextType::class, array(
                'label' => 'label.user',
                'required' => true,
                'mapped' => false,
                'data' => $options['config']['user']->getEmail(),
                'attr' => array(
                    'readonly' => true,
                )))
            ->add('role', TextType::class, array(
                'label' => 'label.role',
                'required' => true,
                'mapped' => false,
                'attr' => array(
                    'placeholder' => 'placeholder.role',
                )))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'intention' => 'UserAddRoleForm',
            'translation_domain' => 'user',
        ));

        $resolver->setRequired('config');
    }
}