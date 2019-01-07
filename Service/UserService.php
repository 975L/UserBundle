<?php
/*
 * (c) 2018: 975L <contact@975l.com>
 * (c) 2018: Laurent Marquet <laurent.marquet@laposte.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace c975L\UserBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Exception\DisabledException;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use c975L\ConfigBundle\Service\ConfigServiceInterface;
use c975L\ServicesBundle\Service\ServiceToolsInterface;
use c975L\UserBundle\Service\Email\UserEmailInterface;
use c975L\UserBundle\Service\UserServiceInterface;

/**
 * Main services related to User
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 * @copyright 2018 975L <contact@975l.com>
 */
class UserService implements UserServiceInterface
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
     * @var Request
     */
    private $request;

    /**
     * Stores RouterInterface
     * @var RouterInterface
     */
    private $router;

    /**
     * Stores UserEmailInterface
     * @var UserEmailInterface
     */
    private $userEmail;

    /**
     * Stores ServiceToolsInterface
     * @var ServiceToolsInterface
     */
    private $serviceTools;

    public function __construct(
        ConfigServiceInterface $configService,
        EntityManagerInterface $em,
        UserPasswordEncoderInterface $passwordEncoder,
        RequestStack $requestStack,
        RouterInterface $router,
        ServiceToolsInterface $serviceTools,
        UserEmailInterface $userEmail
    )
    {
        $this->configService = $configService;
        $this->em = $em;
        $this->passwordEncoder = $passwordEncoder;
        $this->request = $requestStack->getCurrentRequest();
        $this->router = $router;
        $this->serviceTools = $serviceTools;
        $this->userEmail = $userEmail;
    }

    /**
     * {@inheritdoc}
     */
    public function add($user)
    {
        //UserLight Entity
        $user
            ->setIdentifier(md5($user->getEmail() . uniqid(time())))
            ->setCreation(new \DateTime())
            ->setEnabled(false)
            ->setPassword($this->passwordEncoder->encodePassword($user, $user->getPlainPassword()))
            ->setPlainPassword(null)
            ->setToken(hash('sha1', $user->getEmail() . uniqid()))
        ;

        //For other entities
        if (method_exists($user, 'setAvatar')) {
            $user->setAvatar('https://www.gravatar.com/avatar/' . hash('md5', strtolower(trim($user->getEmail()))) . '?s=512&d=mm&r=g');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function addAttempt($error)
    {
        $attempt = null;
        $disabledSubmit = '';

        if ($this->configService->getParameter('c975LUser.signinAttempts') > 0 && !$error instanceof DisabledException) {
            $delayDisable = '+15 minutes';
            $session = $this->request->getSession();
            $configSigninAttempts = $this->configService->getParameter('c975LUser.signinAttempts');

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

    /**
     * {@inheritdoc}
     */
    public function addRole($user, string $role)
    {
        $user->addRole($role);

        $this->em->persist($user);
        $this->em->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function archive($userId)
    {
        if ($this->configService->getParameter('c975LUser.archiveUser')) {
            $conn = $this->em->getConnection();
            $query = 'CALL sp_UserArchive("' . $userId . '");';
            $stmt = $conn->prepare($query);
            $stmt->execute();
            $stmt->closeCursor();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function checkProfile($user)
    {
        if ((!empty($this->configService->getParameter('c975LUser.multilingual')) && null === $user->getLocale()) ||
            ($this->configService->getParameter('c975LUser.address') && null === $user->getAddress()) ||
            ($this->configService->getParameter('c975LUser.business') && null === $user->getBusinessType())
        ) {
            //Creates flash
            $this->serviceTools->createFlash('user', 'text.update_profile_missing_info', 'warning');

            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function delete($user)
    {
        //Archives user
        $this->archive($user->getId());

        //Removes user from DB
        $this->em->remove($user);
        $this->em->flush();

        //Sends email
        $this->userEmail->send('delete', $user);

        //Creates flash
        $this->serviceTools->createFlash('user', 'text.account_deleted', 'danger');
    }

    /**
     * {@inheritdoc}
     */
    public function deleteRole($user, string $role)
    {
        $user->deleteRole($role);

        $this->em->persist($user);
        $this->em->flush();
    }

    /**
     * {@inheritdoc}
     */
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

    /**
     * {@inheritdoc}
     */
    public function findUserByEmail($email)
    {
        return $this
            ->em
            ->getRepository($this->getUserEntity())
            ->findOneByEmail($email)
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function findUserById($id)
    {
        return $this
            ->em
            ->getRepository($this->getUserEntity())
            ->findOneById($id)
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function findUserByIdentifier($identifier)
    {
        return $this
            ->em
            ->getRepository($this->getUserEntity())
            ->findOneByIdentifier($identifier)
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function findUserBySocialId($socialId)
    {
        return $this
            ->em
            ->getRepository($this->getUserEntity())
            ->findOneBySocialId($socialId)
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function findUserByToken($token)
    {
        return $this
            ->em
            ->getRepository($this->getUserEntity())
            ->findOneByToken($token)
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getUsersAll()
    {
        return $this->em
            ->getRepository($this->getUserEntity())
            ->findAll()
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getUserEntity()
    {
        return $this->configService->getParameter('c975LUser.entity');
    }

    /**
     * {@inheritdoc}
     */
    public function modify($user)
    {
        //Updates data
        if (method_exists($user, 'setAvatar')) {
            $user->setAvatar('https://www.gravatar.com/avatar/' . hash('md5', strtolower(trim($user->getEmail()))) . '?s=512&d=mm&r=g');
        }
        $user->setEnabled($user->getAllowUse());

        //Creates flash
        $this->serviceTools->createFlash('user', 'text.profile_modified');

        //Persists data in DB
        $this->em->persist($user);
        $this->em->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function modifyRoles($user, $roles)
    {
        $roles = json_decode($roles, true);
        if (isset($roles['roles'])) {
            $user->setRoles($roles['roles']);
            $this->em->persist($user);
            $this->em->flush();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function signup($user)
    {
        $this->add($user);

        //Sends email
        $this->userEmail->send('signup', $user);

        //Removes challenge from session
        $this->request->getSession()->remove('challenge');
        $this->request->getSession()->remove('challengeResult');

        //Persists user in DB
        $this->em->persist($user);
        $this->em->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function signupConfirm($user)
    {
        //Updates data
        $user
            ->setToken(null)
            ->setEnabled(true)
            ;

        //Creates flash
        $this->serviceTools->createFlash('user', 'text.signup_confirmed');

        //Persists data in DB
        $this->em->persist($user);
        $this->em->flush();
    }
}
