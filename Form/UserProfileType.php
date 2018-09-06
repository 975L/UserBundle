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
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserProfileType extends AbstractType
{
    private $tokenStorage;

    public function __construct(\Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    //Builds the form
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $user = $this->tokenStorage->getToken()->getUser();
        $disabled = $options['userConfig']['action'] == 'modify' ? false : true;

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
//MULTILINGUAL
        if (!empty($options['userConfig']['multilingual'])) {
            $builder
                ->add('locale', ChoiceType::class, array(
                    'label' => 'label.locale',
                    'disabled' => $disabled,
                    'required' => true,
                    'multiple' => false,
                    'placeholder' => 'label.locale',
                    'choices'  => $options['userConfig']['multilingual'],
                    ))
                ;
        }
//ADDRESS
        if ($options['userConfig']['address']) {
            $builder
                ->add('address', TextType::class, array(
                    'label' => 'label.adress',
                    'disabled' => $disabled,
                    'required' => true,
                    ))
                ->add('address2', TextType::class, array(
                    'label' => 'label.adress2',
                    'disabled' => $disabled,
                    'required' => true,
                    ))
                ->add('postal', TextType::class, array(
                    'label' => 'label.postal',
                    'disabled' => $disabled,
                    'required' => true,
                    ))
                ->add('town', TextType::class, array(
                    'label' => 'label.town',
                    'disabled' => $disabled,
                    'required' => true,
                    ))
                ->add('country', TextType::class, array(
                    'label' => 'label.country',
                    'disabled' => $disabled,
                    'required' => true,
                    ))
            ;
        }
//BUSINESS
        if ($options['userConfig']['business']) {
            $builder
                ->add('businessType', ChoiceType::class, array(
                    'label' => 'label.type',
                    'disabled' => $disabled,
                    'required' => true,
                    'expanded' => true,
                    'multiple' => false,
                    'choices'  => array(
                        'label.individual' => 'individual',
                        'label.association' => 'association',
                        'label.business' => 'business',
                        ),
                    'label_attr' => array(
                        'class' => 'radio-inline'
                    ),
                    ))
            ;
            if ($user->getBusinessType() != 'individual') {
                $builder
                    ->add('businessName', TextType::class, array(
                        'label' => 'label.business_name',
                        'disabled' => $disabled,
                        'required' => true,
                        'attr' => array(
                            'placeholder' => 'label.business_name',
                        )))
                    ->add('businessAddress', TextType::class, array(
                        'label' => 'label.address',
                        'disabled' => $disabled,
                        'required' => true,
                        'attr' => array(
                            'placeholder' => 'label.address',
                        )))
                    ->add('businessAddress2', TextType::class, array(
                        'label' => 'label.address2',
                        'disabled' => $disabled,
                        'required' => false,
                        'attr' => array(
                            'placeholder' => 'label.address2',
                        )))
                    ->add('businessPostal', TextType::class, array(
                        'label' => 'label.postal',
                        'disabled' => $disabled,
                        'required' => true,
                        'attr' => array(
                            'placeholder' => 'label.postal',
                        )))
                    ->add('businessTown', TextType::class, array(
                        'label' => 'label.town',
                        'disabled' => $disabled,
                        'required' => true,
                        'attr' => array(
                            'placeholder' => 'label.town',
                        )))
                    ->add('businessCountry', TextType::class, array(
                        'label' => 'label.country',
                        'disabled' => $disabled,
                        'required' => true,
                        'attr' => array(
                            'placeholder' => 'label.country',
                        )))
                ;
            }
            if ($user->getBusinessType() == 'business') {
                $builder
                    ->add('businessSiret', TextType::class, array(
                        'label' => 'label.siret',
                        'disabled' => $disabled,
                        'required' => true,
                        'attr' => array(
                            'placeholder' => 'label.siret',
                        )))
                    ->add('businessVat', TextType::class, array(
                        'label' => 'label.vat',
                        'disabled' => $disabled,
                        'required' => true,
                        'attr' => array(
                            'placeholder' => 'label.vat',
                        )))
                ;
            }
        }
//SOCIAL
        if ($options['userConfig']['social']) {
            if ($user->getSocialNetwork() !== null) {
                $builder
                    ->add('socialNetwork', TextType::class, array(
                        'label' => 'label.social_network',
                        'required' => false,
                        'disabled' => true,
                        ))
                ;
            }
        }
//ALLOW USE
        $builder
            ->add('allowUse', CheckboxType::class, array(
                'label' => 'label.allow_use',
                'required' => false,
            ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'intention' => 'UserForm',
            'allow_extra_fields' => true,
            'translation_domain' => 'user',
        ));

        $resolver->setRequired('userConfig');
    }
}