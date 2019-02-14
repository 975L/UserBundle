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
use Symfony\Component\Security\Core\User\UserInterface;
use c975L\UserBundle\Entity\Traits\UserLightTrait;
use c975L\UserBundle\Entity\Traits\UserDefaultTrait;
use c975L\UserBundle\Entity\Traits\UserBusinessTrait;

/**
 * Entity UserBusinessAbstract
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 * @copyright 2018 975L <contact@975l.com>
 *
 * @ORM\MappedSuperclass
 */
abstract class UserBusinessAbstract implements UserInterface
{
    use UserLightTrait;
    use UserDefaultTrait;
    use UserBusinessTrait;

    const ROLE_DEFAULT = 'ROLE_USER';

    /**
     * User unique id
     * @var int
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(name="id", type="integer")
     */
    private $id;
}
