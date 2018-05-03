<?php
/*
 * (c) 2018: 975L <contact@975l.com>
 * (c) 2018: Laurent Marquet <laurent.marquet@laposte.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace c975L\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use c975L\UserBundle\Entity\UserAbstract;

/**
 * User
 *
 * @ORM\Table(name="user", indexes={
 *      @ORM\Index(name="un_email", columns={"name", "email"}),
 *      @ORM\Index(name="un_identifier", columns={"name", "identifier"}),
 * })
 * @ORM\Entity(repositoryClass="c975L\UserBundle\Repository\UserRepository")
 */
class User extends UserAbstract
{
}