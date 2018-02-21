<?php
/*
 * (c) 2018: 975l <contact@975l.com>
 * (c) 2018: Laurent Marquet <laurent.marquet@laposte.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace c975L\UserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserProfileType extends AbstractType
{
    //Builds the form
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $disabled = $options['data']->getAction() == 'modify' ? false : true;

//avatar

        $builder
            ->remove('current_password')
            ->add('email', EmailType::class, array(
                'label' => 'label.email',
                'disabled' => $disabled,
                'required' => true,
                'attr' => array(
                    'placeholder' => 'placeholder.email',
                )))
            ->add('creation', DateTimeType::class, array(
                'label' => 'label.creation',
                'date_widget' => 'single_text',
                'time_widget' => 'single_text',
                'disabled' => true,
                'required' => false,
                ))
            ->add('latestSignin', DateTimeType::class, array(
                'label' => 'label.latest_signin',
                'date_widget' => 'single_text',
                'time_widget' => 'single_text',
                'disabled' => true,
                'required' => false,
                ))
            ->add('latestSignout', DateTimeType::class, array(
                'label' => 'label.latest_signout',
                'date_widget' => 'single_text',
                'time_widget' => 'single_text',
                'disabled' => true,
                'required' => false,
                ))
            ->add('gender', ChoiceType::class, array(
                'label' => 'label.gender',
                'disabled' => $disabled,
                'required' => false,
                'choices'  => array(
                    'label.gender' => null,
                    'label.woman' => 'woman',
                    'label.man' => 'man',
                )))
            ->add('firstname', TextType::class, array(
                'label' => 'label.firstname',
                'disabled' => $disabled,
                'required' => true,
                'attr' => array(
                    'placeholder' => 'placeholder.firstname',
                )))
            ->add('lastname', TextType::class, array(
                'label' => 'label.lastname',
                'disabled' => $disabled,
                'required' => false,
                'attr' => array(
                    'placeholder' => 'placeholder.lastname',
                )))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'c975L\UserBundle\Entity\User',
            'intention' => 'UserForm',
            'allow_extra_fields' => true,
            'translation_domain' => 'user',
        ));
    }
}
