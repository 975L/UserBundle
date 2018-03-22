<?php
/*
 * (c) 2018: 975l <contact@975l.com>
 * (c) 2018: Laurent Marquet <laurent.marquet@laposte.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace c975L\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use c975L\UserBundle\Entity\UserAbstract;

/**
 * UserAddressAbstract
 */
abstract class UserAddressAbstract extends UserAbstract
{
    /**
     * @ORM\Column(name="address", type="string", nullable=true)
     */
    protected $address;

    /**
     * @ORM\Column(name="address2", type="string", nullable=true)
     */
    protected $address2;

    /**
     * @ORM\Column(name="postal", type="string", nullable=true)
     */
    protected $postal;

    /**
     * @ORM\Column(name="town", type="string", nullable=true)
     */
    protected $town;

    /**
     * @ORM\Column(name="country", type="string", nullable=true)
     */
    protected $country;
}