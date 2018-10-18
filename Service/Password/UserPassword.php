<?php
/*
 * (c) 2018: 975L <contact@975l.com>
 * (c) 2018: Laurent Marquet <laurent.marquet@laposte.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace c975L\UserBundle\Service\Password;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Doctrine\ORM\EntityManagerInterface;
use c975L\ConfigBundle\Service\ConfigServiceInterface;
use c975L\ServicesBundle\Service\ServiceToolsInterface;
use c975L\UserBundle\Service\Password\UserPasswordInterface;
use c975L\UserBundle\Service\Email\UserEmailInterface;

/**
 * Password services related to User
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 * @copyright 2018 975L <contact@975l.com>
 */
class UserPassword implements UserPasswordInterface
{
    /**
     * Stores ConfigServiceInterface
     * @var ConfigServiceInterface
     */
    private $configService;

    /**
     * Stores EntityManagerInterface
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * Stores UserPasswordEncoderInterface
     * @var UserPasswordEncoderInterface
     */
    private $passwordEncoder;

    /**
     * Stores curent Request
     * @var RequestStack
     */
    private $request;

    /**
     * Stores ServiceToolsInterface
     * @var ServiceToolsInterface
     */
    private $serviceTools;

    /**
     * Stores UserEmailInterface
     * @var UserEmailInterface
     */
    private $userEmail;

    /**
     * Used to define the delay (2 hours) to allow password reset
     * @var string
     */
    public const DELAY = 'PT2H';

    public function __construct(
        ConfigServiceInterface $configService,
        EntityManagerInterface $em,
        UserPasswordEncoderInterface $passwordEncoder,
        RequestStack $requestStack,
        ServiceToolsInterface $serviceTools,
        UserEmailInterface $userEmail
    )
    {
        $this->configService = $configService;
        $this->em = $em;
        $this->passwordEncoder = $passwordEncoder;
        $this->request = $requestStack->getCurrentRequest();
        $this->serviceTools = $serviceTools;
        $this->userEmail = $userEmail;
    }

    /**
     * {@inheritdoc}
     */
    public function change($user)
    {
        //Adds data to user
        $user
            ->setPassword($this->passwordEncoder->encodePassword($user, $user->getPlainPassword()))
            ->setPlainPassword(null)
        ;

        //Creates flash
        $this->serviceTools->createFlash('user', 'text.password_changed');

        //Sends email
        $this->userEmail->send('change-password-confirm', $user);

        //Persists data in DB
        $this->em->persist($user);
        $this->em->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function delayExpired($user)
    {
        //Removes challenge from session in case signup has been called before
        $session = $this->request->getSession();
        $session->remove('challenge');
        $session->remove('challengeResult');

        //Request not already sent or is out of <time datetime="_"></time>
        $delayReset = new \DateInterval(self::DELAY);
        if ($user->getPasswordRequest() instanceof \DateTime && $user->getPasswordRequest()->add($delayReset) < new \DateTime()) {
            //Removes data from user
            $user
                ->setToken(null)
                ->setPasswordRequest(null)
            ;

            //Persists data in DB
            $this->em->persist($user);
            $this->em->flush();

            //Creates flash
            $this->serviceTools->createFlash('user', 'text.link_has_expired', 'danger');

            return true;
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function resetConfirm($user)
    {
        //Adds data to user
        $user
            ->setPassword($this->passwordEncoder->encodePassword($user, $user->getPlainPassword()))
            ->setPlainPassword(null)
            ->setToken(null)
            ->setPasswordRequest(null)
        ;

        //Creates flash
        $this->serviceTools->createFlash('user', 'text.password_reset_success');

        //Sends email
        $this->userEmail->send('change-password-confirm', $user);

        //Persists data in DB
        $this->em->persist($user);
        $this->em->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function resetRequest(Request $request, $formData)
    {
        //Gets user
        $email = strtolower($request->request->get('user_reset_password')['email']);
        $user = $this->em
            ->getRepository($this->configService->getParameter('c975LUser.entity'))
            ->findOneByEmail($email)
        ;

        //Updates data
        if (is_subclass_of($user, 'c975L\UserBundle\Entity\UserAbstract')) {
            //Request not already sent or is out of time
            $delayReset = new \DateInterval(self::DELAY);
            if (null === $user->getPasswordRequest() ||
                ($user->getPasswordRequest() instanceof \DateTime && $user->getPasswordRequest()->add($delayReset) < new \DateTime())
            ) {
                //Adds data to user
                $user
                    ->setPasswordRequest(new \DateTime())
                    ->setToken(hash('sha1', $user->getEmail() . uniqid()))
                ;

                //Sends email
                $this->userEmail->send('reset-password-request', $user, compact('delayReset'));

                //Persists data in DB
                $this->em->persist($user);
                $this->em->flush();
            }
        }

        //Defines data for check email page (Page will always be displayed, even if user is not found, to unallow finding registered users)
        $session = $request->getSession();
        $session->set('checkEmailUser', $formData['email']);
        $session->set('checkEmailUserAction', 'resetPassword');
    }
}