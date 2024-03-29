<?php
/*
 * (c) 2018: 975L <contact@975l.com>
 * (c) 2018: Laurent Marquet <laurent.marquet@laposte.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace c975L\UserBundle\Service\Email;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;
use c975L\ConfigBundle\Service\ConfigService;
use c975L\EmailBundle\Service\EmailService;
use c975L\UserBundle\Service\Email\UserEmailInterface;

/**
 * Main services related to User
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 * @copyright 2018 975L <contact@975l.com>
 */
class UserEmail implements UserEmailInterface
{
    /**
     * Stores ConfigService
     * @var ConfigService
     */
    private $configService;

    /**
     * Stores EmailService
     * @var EmailService
     */
    private $emailService;

    /**
     * Stores current Request
     * @var Request
     */
    private $request;

    /**
     * Stores RouterInterface
     * @var RouterInterface
     */
    private $router;

    /**
     * Stores Environment
     * @var Environment
     */
    private $environment;

    /**
     * Stores TranslatorInterface
     * @var TranslatorInterface
     */
    private $translator;

    public function __construct(
        ConfigService $configService,
        EmailService $emailService,
        RequestStack $requestStack,
        RouterInterface $router,
        Environment $environment,
        TranslatorInterface $translator
    )
    {
        $this->configService = $configService;
        $this->emailService = $emailService;
        $this->request = $requestStack->getCurrentRequest();
        $this->router = $router;
        $this->environment = $environment;
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     */
    public function send($object, $user, $options = array())
    {
        //Defines locale
        $locale = null === $user->getLocale() ? 'en' : $user->getLocale();

        //Change (or reset) password confirm
        if('change-password-confirm' === $object) {
            $subject = $this->translator->trans('label.change_password', array(), 'user');
            $body = $this->environment->render('@c975LUser/emails/changedPassword.html.twig', array(
                'locale' => $locale,
            ));
        //Delete account
        } elseif ('delete' === $object) {
            $subject = $this->translator->trans('label.delete_account', array(), 'user');
            $body = $this->environment->render('@c975LUser/emails/delete.html.twig', array(
                'locale' => $locale,
            ));
        //Reset password request
        } elseif('reset-password-request' === $object) {
            $subject = $this->translator->trans('label.reset_password', array(), 'user');
            $expiryDate = new \DateTime();
            $body = $this->environment->render('@c975LUser/emails/resetPasswordRequest.html.twig', array(
                'url' => $this->router->generate('user_reset_password_confirm', array('token' => $user->getToken()), UrlGeneratorInterface::ABSOLUTE_URL),
                'date' => $expiryDate->add($options['delayReset']), //Got from Service/Password/UserPassword->resetRequest()
                'locale' => $locale,
                'user' => $user,
            ));
        //Signup
        } elseif('signup' === $object) {
            $subject = $this->translator->trans('label.signup_email', array(), 'user');
            $body = $this->environment->render('@c975LUser/emails/signup.html.twig', array(
                'url' => $this->router->generate('user_signup_confirm', array('token' => $user->getToken()), UrlGeneratorInterface::ABSOLUTE_URL),
                'locale' => $locale,
                'user' => $user,
            ));
        }

        //Sends email
        if (isset($subject) && isset($body)) {
            $emailData = array(
                'subject' => $subject,
                'sentFrom' => $this->configService->getParameter('c975LEmail.sentFrom'),
                'sentTo' => $user->getEmail(),
                'replyTo' => $this->configService->getParameter('c975LEmail.sentFrom'),
                'body' => $body,
                'ip' => $this->request->getClientIp(),
                );
            return $this->emailService->send($emailData, $this->configService->getParameter('c975LUser.databaseEmail'));
        }

        return false;
    }
}