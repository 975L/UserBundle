<?php
/*
 * (c) 2018: 975L <contact@975l.com>
 * (c) 2018: Laurent Marquet <laurent.marquet@laposte.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace c975L\UserBundle\Service;

use DateInterval;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\DisabledException;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Signer\Keychain;
use Lcobucci\JWT\Signer\Rsa\Sha512;
use c975L\ConfigBundle\Service\ConfigServiceInterface;
use c975L\UserBundle\Service\ApiServiceInterface;
use c975L\UserBundle\Service\UserServiceInterface;

/**
 * Main services related to API
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 * @copyright 2018 975L <contact@975l.com>
 */
class ApiService implements ApiServiceInterface
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
     * Stores Keychain
     * @var Keychain
     */
    private $keychain;

    /**
     * Stores UserPasswordEncoderInterface
     * @var UserPasswordEncoderInterface
     */
    private $passwordEncoder;

    /**
     * Stores RouterInterface
     * @var RouterInterface
     */
    private $router;

    /**
     * Stores Sha512
     * @var Sha512
     */
    private $signer;

    /**
     * Stores UserServiceInterface
     * @var UserServiceInterface
     */
    private $userService;

    /**
     * Used to define the delay (2 hours) to allow password reset
     * @var string
     */
    public const DELAY = 'PT2H';

    public function __construct(
        ConfigServiceInterface $configService,
        EntityManagerInterface $em,
        RouterInterface $router,
        UserPasswordEncoderInterface $passwordEncoder,
        UserServiceInterface $userService
    )
    {
        $this->configService = $configService;
        $this->em = $em;
        $this->keychain = new Keychain();
        $this->passwordEncoder = $passwordEncoder;
        $this->router = $router;
        $this->signer = new Sha512();
        $this->userService = $userService;
    }

    /**
     * Allows user to change its password by submitting a new one
     */
    public function changePassword($user, $parameters)
    {
        $parameters = json_decode($parameters, true);
        if (array_key_exists('plainPassword', $parameters)) {
            $user
                ->setPassword($this->passwordEncoder->encodePassword($user, $parameters['plainPassword']))
                ->setPlainPassword(null)
                ->setToken(null)
            ;

            $this->em->persist($user);
            $this->em->flush();

            return $user->toArray();
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function create($user, ParameterBag $parameters)
    {
        if (null !== $parameters->get('plainPassword')) {
            $this->hydrate($user, $parameters);
            $this->userService->add($user);
            $user
                ->setAllowUse(true)
                ->setEnabled(true)
                ->setToken(null)
            ;

            $this->em->persist($user);
            $this->em->flush();

            return $user->toArray();
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function delete($user)
    {
        //Archives user
        $this->userService->archive($user->getId());

        //Removes user from DB
        $this->em->remove($user);
        $this->em->flush();
    }

    /**
     * Returns the list of all users in the array format
     * @return array
     */
    public function findAll()
    {
        return $this->em
            ->getRepository($this->configService->getParameter('c975LUser.entity'))
            ->findAll()
        ;
    }

    /**
     * Searches the term in the User collection
     * @return array
     */
    public function findAllSearch(string $term)
    {
        return $this->em
            ->getRepository($this->configService->getParameter('c975LUser.entity'))
            ->findAllSearch($term)
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getToken($user, Request $request)
    {
        $builder = new Builder();
        $privateKey = $this->configService->getParameter('c975LUser.privateKey');
        $privateKey = '/' === substr($privateKey, 0, 1) ? $privateKey : '/' . $privateKey;
        $privateKey = $this->configService->getContainerParameter('kernel.project_dir') . $privateKey;

        //Sets expiration time for JWT using default OR data sent to authenticte Route
        $dataRequest = json_decode($request->getContent(), true);
        $expiration = array_key_exists('expiration', $dataRequest) && 0 < $dataRequest['expiration'] ? $dataRequest['expiration'] : time() + 4 * 60 * 60;

        //Builds token
        $token = $builder
            ->setIssuer($this->configService->getParameter('c975LCommon.site'))
            ->setId(sha1($user->getIdentifier()), true)
            ->setIssuedAt(time())
            ->setExpiration($expiration)
            ->set('sub', $user->getIdentifier())
            ->sign($this->signer,  $this->keychain->getPrivateKey('file://' . $privateKey))
            ->getToken();

        return $token->__toString();
    }

    /**
     * {@inheritdoc}
     */
    public function hydrate($user, $parameters)
    {
        foreach ($parameters as $key => $value) {
            $method = 'set' . ucfirst($key);
            if ('setIdentifier' !== $method && method_exists($user, $method)) {
                $user->$method(htmlspecialchars($value));
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function modify($user, $parameters)
    {
        $parameters = json_decode($parameters, true);
        $this->hydrate($user, $parameters);
        $this->userService->modify($user);
    }

    /**
     * Allows user to reset its password by setting a token to call the reset confirm Route
     * @returns false|array
     */
    public function resetPassword($user)
    {
        //Request not already sent or is out of time
        $delayReset = new DateInterval(self::DELAY);
        if (null === $user->getPasswordRequest() ||
            ($user->getPasswordRequest() instanceof DateTime && $user->getPasswordRequest()->add($delayReset) < new DateTime())
        ) {
            //Adds data to user
            $token = hash('sha1', $user->getEmail() . uniqid());
            $validity = new DateTime();
            $user
                ->setToken($token)
                ->setPasswordRequest($validity)
            ;

            //Persists data in DB
            $this->em->persist($user);
            $this->em->flush();

            //Returns data
            return array(
                'token' => $token,
                'validity' => $validity->add($delayReset),
            );
        }

        return false;
    }

    /**
     * Reset and change the user password
     * @returns false|array
     */
    public function resetPasswordConfirm($user, $parameters)
    {
        //Checks if password can be reset
        $parameters = json_decode($parameters, true);
        if (array_key_exists('plainPassword', $parameters)) {
            //Checks if request is in time
            $delayReset = new DateInterval(self::DELAY);
            if ($user->getPasswordRequest() instanceof DateTime && $user->getPasswordRequest()->add($delayReset) > new DateTime()) {
                //Adds data to user
                $user
                    ->setPassword($this->passwordEncoder->encodePassword($user, $parameters['plainPassword']))
                    ->setPlainPassword(null)
                    ->setToken(null)
                    ->setPasswordRequest(null)
                ;

                //Persists data in DB
                $this->em->persist($user);
                $this->em->flush();

                return $user->toArray();
            }
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function validateToken(string $token)
    {
        $parser = new Parser();
        $token = $parser->parse((string) $token);
        $publicKey = $this->configService->getParameter('c975LUser.publicKey');
        $publicKey = '/' === substr($publicKey, 0, 1) ? $publicKey : '/' . $publicKey;
        $publicKey = $this->configService->getContainerParameter('kernel.project_dir') . $publicKey;

        if ($token->verify($this->signer, $this->keychain->getPublicKey('file://' . $publicKey))) {
            return $token;
        }

        return null;
    }
}
