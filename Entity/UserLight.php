<?php
/*
 * (c) 2018: 975L <contact@975l.com>
 * (c) 2018: Laurent Marquet <laurent.marquet@laposte.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace c975L\UserBundle\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use c975L\UserBundle\Entity\UserLightAbstract;

/**
 * Entity UserLight (linked to DB table `user`)
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 * @copyright 2018 975L <contact@975l.com>
 *
 * @ORM\Table(name="user", indexes={
 *      @ORM\Index(name="un_email", columns={"email"}),
 *      @ORM\Index(name="un_identifier", columns={"identifier"}),
 * })
 * @ORM\Entity(repositoryClass="c975L\UserBundle\Repository\UserRepository")
 */
class UserLight extends UserLightAbstract
{
}
