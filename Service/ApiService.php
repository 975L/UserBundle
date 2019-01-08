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
use Symfony\Component\Routing\RouterInterface;
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

    public function __construct(
        ConfigServiceInterface $configService,
        EntityManagerInterface $em,
        RouterInterface $router,
        UserServiceInterface $userService
    )
    {
        $this->configService = $configService;
        $this->em = $em;
        $this->keychain = new Keychain();
        $this->router = $router;
        $this->signer = new Sha512();
        $this->userService = $userService;
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
dump($user);
dump('here');die;

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
    public function getToken($user)
    {
        $builder = new Builder();
        $privateKey = $this->configService->getParameter('c975LUser.privateKey');
        $privateKey = '/' === substr($privateKey, 0, 1) ? $privateKey : '/' . $privateKey;
        $privateKey = $this->configService->getContainerParameter('kernel.project_dir') . $privateKey;

        $token = $builder
            ->setIssuer($this->configService->getParameter('c975LCommon.site'))
            ->setId(sha1($user->getIdentifier()), true)
            ->setIssuedAt(time())
            ->setExpiration(time() + 4 * 60 * 60)
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
