<?php
/*
 * (c) 2018: 975L <contact@975l.com>
 * (c) 2018: Laurent Marquet <laurent.marquet@laposte.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace c975L\UserBundle\Repository;

use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;
use Doctrine\ORM\EntityRepository;
use c975L\UserBundle\Entity\User;

/**
 * Repository for User Entity
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 * @copyright 2018 975L <contact@975l.com>
 */
class UserRepository extends EntityRepository implements UserLoaderInterface
{
    /**
     * Returns all the users in an array
     */
    public function findAll()
    {
        return $this->createQueryBuilder('u')
            ->orderBy('u.email', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * Returns all the users corresponding to the searched term in th email field
     */
    public function findAllSearch(string $term)
    {
        return $this->createQueryBuilder('u')
            ->where('LOWER(u.email) LIKE :term')
            ->orderBy('u.email', 'ASC')
            ->setParameter('term', '%' . strtolower($term) . '%')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * Loads User
     * @return mixed
     */
    public function loadUserByUsername($email)
    {
        return $this->createQueryBuilder('u')
            ->where('u.email = :email')
            ->setParameter('email', strtolower($email))
            ->getQuery()
            ->getOneOrNullResult();
    }
}