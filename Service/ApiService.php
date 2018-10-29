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
     * Stores EntityManagerInterface
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * Stores RouterInterface
     * @var RouterInterface
     */
    private $router;

    /**
     * Stores UserServiceInterface
     * @var UserServiceInterface
     */
    private $userService;

    public function __construct(
        EntityManagerInterface $em,
        RouterInterface $router,
        UserServiceInterface $userService
    )
    {
        $this->em = $em;
        $this->router = $router;
        $this->userService = $userService;
    }

    /**
     * {@inheritdoc}
     */
    public function create($user, ParameterBag $parameters)
    {
        if (null !== $parameters->get('email') && null !== $parameters->get('plainPassword')) {
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
     * {@inheritdoc}
     */
    public function hydrate($user, ParameterBag $parameters)
    {
        foreach ($parameters as $key => $value) {
            $method = 'set' . ucfirst($key);
            if ('setIdentifier' !== $method && method_exists($user, $method)) {
                $user->$method($value);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function modify($user, ParameterBag $parameters = null)
    {
        $this->hydrate($user, $parameters);

        if (method_exists($user, 'setAvatar')) {
            $user->setAvatar('https://www.gravatar.com/avatar/' . hash('md5', strtolower(trim($user->getEmail()))) . '?s=512&d=mm&r=g');
        }

        $user->setEnabled($user->getAllowUse());

        //Persists in DB
        $this->em->persist($user);
        $this->em->flush();
    }
}
