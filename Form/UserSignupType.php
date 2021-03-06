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
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * UserSignup FormType
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 * @copyright 2018 975L <contact@975l.com>
 */
class UserSignupType extends AbstractType
{
    /**
     * Stores current session
     * @var string
     */
    private $session;

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->session = $options['config']['session'];

        $builder
            ->add('email', EmailType::class, array(
                'label' => 'label.email',
                'required' => true,
                'attr' => array(
                    'placeholder' => 'placeholder.email',
                )))
            ->add('plainPassword', RepeatedType::class, array(
                'type' => PasswordType::class,
                'invalid_message' => 'text.password_mismatch',
                'options' => array(
                    'attr' => array(
                        'autocomplete' => 'new-password',
                        'class' => 'password-field',
                        )
                    ),
                'required' => true,
                'first_options'  => array('label' => 'label.password'),
                'second_options' => array('label' => 'label.password_repeat'),
                ))
        ;
        if (method_exists($options['data'], 'setFirstname')) {
            $builder
                ->add('firstname', TextType::class, array(
                    'label' => 'label.firstname',
                    'required' => true,
                    'attr' => array(
                        'placeholder' => 'placeholder.firstname',
                    )));
        }
        $builder
            ->add('challenge', TextType::class, array(
                'label' => 'label.challenge',
                'required' => true,
                'attr' => array(
                    'placeholder' => $this->challenge(),
                    'value' => '',
                )))
            ->add('allowUse', CheckboxType::class, array(
                'label' => 'label.allow_use',
                'required' => true,
            ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'intention' => 'UserSignupForm',
            'allow_extra_fields' => true,
            'translation_domain' => 'user',
        ));

        $resolver->setRequired('config');
    }

    /**
     * Defines a challenge (letters or numbers) to be resolved by user in order to avoid bots and captcha
     * @return string
     */
    public function challenge()
    {
        //Defines challenge if not already in session
        if (null === $this->session->get('challenge')) {
            // Defines variables
            $charactersSet = array('letters', 'numbers');
            $characterSet = $charactersSet[mt_rand(0, 1)];
            $operations = array('addition', 'subtraction');
            $operation = $operations[mt_rand(0, 1)];
            $sign = $operation === 'subtraction' ? ' - ' : ' + ';

            //Builds the challenge
            $arrayChallenge = 'numbers' === $characterSet ? $this->challengeNumbers($operation) : $this->challengeLetters($operation);
            list(
                $symbolsA,
                $symbolsB,
                $resultOperation
            ) = $arrayChallenge;

            // Replace a character (must be second or result in case of subtraction of letters, otherwise it's impossible to guess)
            $character = 'letters' === $characterSet && 'subtraction' === $operation ? mt_rand(2, 3) : mt_rand(1, 3);
            if (1 === $character) {
                $challenge = '?' . $sign . $symbolsB .' = ' . $resultOperation;
                $result = $symbolsA;
            } elseif (2 === $character) {
                $challenge = $symbolsA . $sign . '?' .' = ' . $resultOperation;
                $result = $symbolsB;
            } else {
                $challenge = $symbolsA . $sign . $symbolsB .' = ' . '?';
                $result = $resultOperation;
            }

            // Saves the result in the session
            $this->session->set('challenge', $challenge);
            $this->session->set('challengeResult', $result);
        } else {
            $challenge = $this->session->get('challenge');
        }

        return $challenge;
    }

    /**
     * Defines a challenge with letters
     * @return array
     */
    public function challengeLetters($operation)
    {
        if ('subtraction' === $operation) {
            $letter1 = chr(mt_rand(65, 71));
            $letter2 = chr(mt_rand(72, 77));
            $letter3 = chr(mt_rand(78, 83));
            $letter4 = chr(mt_rand(84, 90));
            $symbolsA = $letter4 . $letter2 . $letter3 . $letter1;
            $symbolsB = mt_rand(1, 4);

            if (1 === $symbolsB) {
                $symbolsB = $letter4;
            } elseif (2 === $symbolsB) {
                $symbolsB = $letter2;
            } elseif (3 === $symbolsB) {
                $symbolsB = $letter3;
            } else {
                $symbolsB = $letter1;
            }

            $resultOperation = str_replace($symbolsB, '', $symbolsA);
        } else {
            $letter1 = chr(mt_rand(65, 77));
            $letter2 = chr(mt_rand(78, 90));
            $symbolsA = $letter1 . $letter2;

            $letter1 = chr(mt_rand(65, 77));
            $letter2 = chr(mt_rand(78, 90));
            $symbolsB = $letter2 . $letter1;

            $resultOperation = $symbolsA . $symbolsB;
        }

        return array($symbolsA, $symbolsB, $resultOperation);
    }

    /**
     * Defines a challenge with numbers
     * @return array
     */
    public function challengeNumbers($operation)
    {
        if ('subtraction' === $operation) {
            $symbolsA = mt_rand(50, 100);
            $symbolsB = mt_rand(1, 50);

            $resultOperation = $symbolsA - $symbolsB;
        } else {
            $symbolsA = mt_rand(1, 50);
            $symbolsB = mt_rand(1, 50);

            $resultOperation = $symbolsA + $symbolsB;
        }

        return array($symbolsA, $symbolsB, $resultOperation);
    }
}
