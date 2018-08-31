<?php
/*
 * (c) 2018: 975L <contact@975l.com>
 * (c) 2018: Laurent Marquet <laurent.marquet@laposte.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace c975L\UserBundle\Service;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Exception\DisabledException;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class UserService
{
    private $authUtils;
    private $container;
    private $emailService;
    private $em;
    private $passwordEncoder;
    private $request;
    private $router;
    private $templating;
    private $translator;

    public function __construct(
        \Symfony\Component\Security\Http\Authentication\AuthenticationUtils $authUtils,
        \Symfony\Component\DependencyInjection\ContainerInterface $container,
        \Doctrine\ORM\EntityManagerInterface $em,
        \c975L\EmailBundle\Service\EmailService $emailService,
        \Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface $passwordEncoder,
        \Symfony\Component\HttpFoundation\RequestStack $requestStack,
        \Symfony\Component\Routing\RouterInterface $router,
        \Twig_Environment $templating,
        \Symfony\Component\Translation\TranslatorInterface $translator
    )
    {
        $this->authUtils = $authUtils;
        $this->container = $container;
        $this->em = $em;
        $this->emailService = $emailService;
        $this->passwordEncoder = $passwordEncoder;
        $this->request = $requestStack->getCurrentRequest();
        $this->router = $router;
        $this->templating = $templating;
        $this->translator = $translator;
    }

    //Adds attempt for signin
    public function addAttempt($error)
    {
        $attempt = null;
        $disabledSubmit = '';

        if ($this->container->getParameter('c975_l_user.signinAttempts') > 0 && !$error instanceof DisabledException) {
            $delayDisable = '+15 minutes';
            $session = $this->request->getSession();
            $configSigninAttempts = $this->container->getParameter('c975_l_user.signinAttempts');

            //Adds attempt if signin didn't work
            if (null !== $error) {
                $session->set('userSigninAttempt', $session->get('userSigninAttempt') + 1);
            }

            //Defines attempt
            $sessionUserSigninAttempt = $session->get('userSigninAttempt');
            if ($sessionUserSigninAttempt > 0) {
                $attempt = $sessionUserSigninAttempt . '/' . $configSigninAttempts;
            }

            //Disables/Enables submit button
            if ($sessionUserSigninAttempt >= $configSigninAttempts) {
                //Defines time submit button will be re-enabled if max attempts (defined in config.yml) has been reached
                if (null === $session->get('userSigninNewAttemptTime')) {
                    $session->set('userSigninNewAttemptTime', new \DateTime($delayDisable));
                }

                //Disables submit button
                if (new \DateTime() < $session->get('userSigninNewAttemptTime')) {
                    $disabledSubmit = 'disabled="disabled"';
                //Enables submit button if delay is finished
                } else {
                    $session->remove('userSigninAttempt');
                    $session->remove('userSigninNewAttemptTime');
                }
            }
        }

        return compact('attempt', 'disabledSubmit');
    }

    //Archives the user using Stored Procedure
    public function archive($userId)
    {
        if ($this->container->getParameter('c975_l_user.archiveUser')) {
            $conn = $this->em->getConnection();
            $query = 'CALL sp_UserArchive("' . $userId . '");';
            $stmt = $conn->prepare($query);
            $stmt->execute();
            $stmt->closeCursor();
        }
    }

    //Changes the password
    public function changePassword($user)
    {
        //Adds data to user
        $user
            ->setPassword($this->passwordEncoder->encodePassword($user, $user->getPlainPassword()))
            ->setPlainPassword(null)
        ;

        //Creates flash
        $this->createFlash('change-password');

        //Sends email
        $this->sendEmail('change-password-confirm', $user);

        //Persists data in DB
        $this->em->persist($user);
        $this->em->flush();
    }

    //Checks if profile is well filled
    public function checkProfile($user)
    {
        if ((!empty($this->container->getParameter('c975_l_user.multilingual')) && null === $user->getLocale()) ||
            ($this->container->getParameter('c975_l_user.address') && null === $user->getAddress()) ||
            ($this->container->getParameter('c975_l_user.business') && null === $user->getBusinessType())
        ) {
            //Creates flash
            $this->createFlash('missing-info');

            return false;
        }

        return true;
    }

    //Creates flash message
    public function createFlash($object)
    {
        $style = 'success';
        switch ($object) {
            case 'change-password':
                $flash = 'text.password_changed';
                break;
            case 'delete':
                $flash = 'text.account_deleted';
                break;
            case 'missing-info':
                $flash = 'text.update_profile_missing_info';
                $style = 'warning';
                break;
            case 'modify':
                $flash = 'text.profile_modified';
                break;
            case 'reset-password-confirm':
                $flash = 'text.password_reset_success';
                break;
            case 'signup-confirm':
                $flash = 'text.signup_confirmed';
                break;
            case 'token-not-found':
                $flash = 'text.token_not_found';
                $style = 'warning';
                break;
        }

        if(isset($flash)) {
            $this->request->getSession()
                ->getFlashBag()
                ->add($style, $this->translator->trans($flash, array(), 'user'))
            ;
        }
    }

    //Deletes the user
    public function delete($user)
    {
        //Archives user
        $this->archive($user->getId());

        //Removes user from DB
        $this->em->remove($user);
        $this->em->flush();

        //Sends email
        $this->sendEmail('delete', $user);

        //Creates flash
        $this->createFlash('delete');
    }

    //Export the user's data
    public function export($user, $format)
    {
        //Defines function to use for DateTime fields
        $callback = function ($dateTime) {
            return $dateTime instanceof \DateTime ? $dateTime->format(\DateTime::ISO8601) : '';
        };

        //Defines Normalizer
        $normalizer = new ObjectNormalizer();
        $normalizer
            ->setIgnoredAttributes(array(
                'accountNonExpired',
                'accountNonLocked',
                'credentialsNonExpired',
                'salt',
                'password',
                'token',
                'passwordRequest',
                'plainPassword',
                'challenge',
            ))
            ->setCallbacks(array(
                'creation' => $callback,
                'latestSignin' => $callback,
                'latestSignout' => $callback,
                'creation' => $callback,
            ))
        ;

        //Defines Encoder
        $encoder = $format === 'json' ? new JsonEncoder() : new XmlEncoder();

        //Defines Response
        $serializer = new Serializer(array($normalizer), array($encoder));
        $response = new Response($serializer->serialize($user, $format));
        $response->headers->set('Content-Type', 'application/' . $format);

        return $response;
    }

    //Finds user by email
    public function findUserByEmail($email)
    {
        return $this->em->getRepository($this->container->getParameter('c975_l_user.entity'))->findOneByEmail($email);
    }

    //Finds user by id
    public function findUserById($id)
    {
        return $this->em->getRepository($this->container->getParameter('c975_l_user.entity'))->findOneById($id);
    }

    //Finds user by identifier
    public function findUserByIdentifier($identifier)
    {
        return $this->em->getRepository($this->container->getParameter('c975_l_user.entity'))->findOneByIdentifier($identifier);
    }

    //Finds user by socialId
    public function findUserBySocialId($socialId)
    {
        return $this->em->getRepository($this->container->getParameter('c975_l_user.entity'))->findOneBySocialId($socialId);
    }

    //Gets the Terms of use url
    public function getTouUrl()
    {
        return $this->getUrl($this->container->getParameter('c975_l_user.touUrl'));
    }

    //Defines the url
    public function getUrl($data)
    {
        //Calculates the url if a Route is provided
        if (false !== strpos($data, ',')) {
            $routeData = $this->getUrlFromRoute($data);
            $url = $this->router->generate($routeData['route'], $routeData['params'], UrlGeneratorInterface::ABSOLUTE_URL);
        //An url has been provided
        } elseif (false !== strpos($data, 'http')) {
            $url = $data;
        //Not valid data
        } else {
            $url = null;
        }

        return $url;
    }

    //Gets url from a Route
    public function getUrlFromRoute($route)
    {
        //Gets Route
        $routeValue = trim(substr($route, 0, strpos($route, ',')), "\'\"");

        //Gets parameters
        $params = trim(substr($route, strpos($route, '{')), "{}");
        $params = str_replace(array('"', "'"), '', $params);
        $params = explode(',', $params);

        //Caculates url
        $paramsArray = array();
        foreach($params as $value) {
            $parameter = explode(':', $value);
            $paramsArray[trim($parameter[0])] = trim($parameter[1]);
        }

        return array(
            'route' => $routeValue,
            'params' => $paramsArray
        );
    }

    //Modifies the user
    public function modify($user)
    {
        //Updates data
        $user
            ->setAvatar('https://www.gravatar.com/avatar/' . hash('md5', strtolower(trim($user->getEmail()))) . '?s=512&d=mm&r=g')
            ->setEnabled($user->getAllowUse())
        ;

        //Creates flash
        $this->createFlash('modify');

        //Persists data in DB
        $this->em->persist($user);
        $this->em->flush();
    }

    //Confirms the rest of password
    public function resetPasswordConfirm($user)
    {
        //Adds data to user
        $user
            ->setPassword($this->passwordEncoder->encodePassword($user, $user->getPlainPassword()))
            ->setPlainPassword(null)
            ->setToken(null)
            ->setPasswordRequest(null)
        ;

        //Creates flash
        $this->createFlash('reset-password-confirm');

        //Sends email
        $this->sendEmail('change-password-confirm', $user);

        //Persists data in DB
        $this->em->persist($user);
        $this->em->flush();
    }

    //Request to reset the password
    public function resetPasswordRequest($user, $formData)
    {
        //Updates data
        if (is_subclass_of($user, 'c975L\UserBundle\Entity\UserAbstract')) {
            //Defines delay for reset (2 hours)
            $delayReset = new \DateInterval('PT2H');

            //Request not already sent or is out of time
            if (null === $user->getPasswordRequest() ||
                ($user->getPasswordRequest() instanceof \DateTime && $user->getPasswordRequest()->add($delayReset) < new \DateTime())
            ) {
                //Adds data to user
                $user
                    ->setPasswordRequest(new \DateTime())
                    ->setToken(hash('sha1', $user->getEmail() . uniqid()))
                ;

                //Sends email
                $this->sendEmail('reset-password-request', $user, compact('delayReset'));

                //Persists data in DB
                $this->em->persist($user);
                $this->em->flush();
            }
        }

        //Defines data for check email page (Page will always be displayed, even if user is not found, to unallow finding registered users)
        $session = $this->request->getSession();
        $session->set('checkEmailUser', $formData['email']);
        $session->set('checkEmailUserAction', 'resetPassword');
    }

    //Sends emails
    public function sendEmail($object, $user, $options = array())
    {
        //Change (or reset) password confirm
        if('change-password-confirm' === $object) {
            $subject = $this->translator->trans('label.change_password', array(), 'user');
            $body = $this->templating->render('@c975LUser/emails/changedPassword.html.twig');
        //Delete account
        } elseif ('delete' === $object) {
            $subject = $this->translator->trans('label.delete_account', array(), 'user');
            $body = $this->templating->render('@c975LUser/emails/delete.html.twig');
        //Reset password request
        } elseif('reset-password-request' === $object) {
            $subject = $this->translator->trans('label.reset_password', array(), 'user');
            $expiryDate = new \DateTime();
            extract($options);
            $body = $this->templating->render('@c975LUser/emails/resetPasswordRequest.html.twig', array(
                'url' => $this->router->generate('user_reset_password_confirm', array('token' => $user->getToken()), UrlGeneratorInterface::ABSOLUTE_URL),
                'date' => $expiryDate->add($delayReset),
                'user' => $user,
            ));
        //Signup
        } elseif('signup' === $object) {
            $subject = $this->translator->trans('label.signup_email', array(), 'user');
            $body = $this->templating->render('@c975LUser/emails/signup.html.twig', array(
                'url' => $this->router->generate('user_signup_confirm', array('token' => $user->getToken()), UrlGeneratorInterface::ABSOLUTE_URL),
                'user' => $user,
            ));
        }

        //Sends email
        if (isset($body)) {
            $emailData = array(
                'subject' => $subject,
                'sentFrom' => $this->container->getParameter('c975_l_email.sentFrom'),
                'sentTo' => $user->getEmail(),
                'sentCc' => null,
                'replyTo' => $this->container->getParameter('c975_l_email.sentFrom'),
                'body' => $body,
                'ip' => $this->request->getClientIp(),
                );
            $this->emailService->send($emailData, $this->container->getParameter('c975_l_user.databaseEmail'));

            return true;
        }

        return false;
    }

    //Registers the user
    public function signup($user)
    {
        //Adds data to user
        $user
            ->setIdentifier(md5($user->getEmail() . uniqid(time())))
            ->setCreation(new \DateTime())
            ->setAvatar('https://www.gravatar.com/avatar/' . hash('md5', strtolower(trim($user->getEmail()))) . '?s=512&d=mm&r=g')
            ->setEnabled(false)
            ->setPassword($this->passwordEncoder->encodePassword($user, $user->getPlainPassword()))
            ->setPlainPassword(null)
            ->setToken(hash('sha1', $user->getEmail() . uniqid()))
        ;

        //Sends email
        $this->sendEmail('signup', $user);

        //Removes challenge from session
        $this->request->getSession()->remove('challenge');
        $this->request->getSession()->remove('challengeResult');

        //Persists user in DB
        $this->em->persist($user);
        $this->em->flush();
    }

    //Confirms user's signup
    public function signupConfirm($user)
    {
        //Updates data
        $user
            ->setToken(null)
            ->setEnabled(true)
            ;

        //Creates flash
        $this->createFlash('signup-confirm');

        //Persists data in DB
        $this->em->persist($user);
        $this->em->flush();
    }
}