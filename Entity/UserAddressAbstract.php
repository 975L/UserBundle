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
use Misd\PhoneNumberBundle\Validator\Constraints\PhoneNumber as AssertPhoneNumber;
use c975L\UserBundle\Entity\UserAbstract;

/**
 * Entity UserAddressAbstract
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 * @copyright 2018 975L <contact@975l.com>
 */
abstract class UserAddressAbstract extends UserAbstract
{
    /**
     * Address for the user
     * @var string
     *
     * @ORM\Column(name="address", type="string", nullable=true)
     */
    protected $address;

    /**
     * Second line address for the user
     * @var string
     *
     * @ORM\Column(name="address2", type="string", nullable=true)
     */
    protected $address2;

    /**
     * Postal code for the user
     * @var string
     *
     * @ORM\Column(name="postal", type="string", nullable=true)
     */
    protected $postal;

    /**
     * Town for the user
     * @var string
     *
     * @ORM\Column(name="town", type="string", nullable=true)
     */
    protected $town;

    /**
     * Country for the user
     * @var string
     *
     * @ORM\Column(name="country", type="string", nullable=true)
     */
    protected $country;

    /**
     * Phone for the user
     * @var string
     *
     * @AssertPhoneNumber
     * @ORM\Column(type="phone_number")
     * @ORM\Column(name="phone", type="string", nullable=true)
     */
    protected $phone;

    /**
     * Fax for the user
     * @var string
     *
     * @AssertPhoneNumber
     * @ORM\Column(type="phone_number")
     * @ORM\Column(name="fax", type="string", nullable=true)
     */
    protected $fax;
}