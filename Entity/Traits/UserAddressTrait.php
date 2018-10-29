<?php
/*
 * (c) 2018: 975L <contact@975l.com>
 * (c) 2018: Laurent Marquet <laurent.marquet@laposte.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace c975L\UserBundle\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;
use Misd\PhoneNumberBundle\Validator\Constraints\PhoneNumber as AssertPhoneNumber;

/**
 * Trait UserAddressTrait
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 * @copyright 2018 975L <contact@975l.com>
 */
trait UserAddressTrait
{
    /**
     * Address for the user
     * @var string
     *
     * @ORM\Column(name="address", type="string", nullable=true)
     */
    private $address;

    /**
     * Second line address for the user
     * @var string
     *
     * @ORM\Column(name="address2", type="string", nullable=true)
     */
    private $address2;

    /**
     * Postal code for the user
     * @var string
     *
     * @ORM\Column(name="postal", type="string", nullable=true)
     */
    private $postal;

    /**
     * Town for the user
     * @var string
     *
     * @ORM\Column(name="town", type="string", nullable=true)
     */
    private $town;

    /**
     * Country for the user
     * @var string
     *
     * @ORM\Column(name="country", type="string", nullable=true)
     */
    private $country;

    /**
     * Phone for the user
     * @var string
     *
     * @AssertPhoneNumber
     * @ORM\Column(type="phone_number")
     * @ORM\Column(name="phone", type="string", nullable=true)
     */
    private $phone;

    /**
     * Fax for the user
     * @var string
     *
     * @AssertPhoneNumber
     * @ORM\Column(type="phone_number")
     * @ORM\Column(name="fax", type="string", nullable=true)
     */
    private $fax;


//GETTERS/SETTERS
    /**
     * Set address
     * @param string
     * @return User
     */
    public function setAddress($address)
    {
        $this->address = $address;
        return $this;
    }

    /**
     * Get address
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Set address2
     * @param string
     * @return User
     */
    public function setAddress2($address2)
    {
        $this->address2 = $address2;
        return $this;
    }

    /**
     * Get address2
     * @return string
     */
    public function getAddress2()
    {
        return $this->address2;
    }

    /**
     * Set postal
     * @param string
     * @return User
     */
    public function setPostal($postal)
    {
        $this->postal = $postal;
        return $this;
    }

    /**
     * Get postal
     * @return string
     */
    public function getPostal()
    {
        return $this->postal;
    }

    /**
     * Set town
     * @param string
     * @return User
     */
    public function setTown($town)
    {
        $this->town = $town;
        return $this;
    }

    /**
     * Get town
     * @return string
     */
    public function getTown()
    {
        return $this->town;
    }

    /**
     * Set country
     * @param string
     * @return User
     */
    public function setCountry($country)
    {
        $this->country = $country;
        return $this;
    }

    /**
     * Get country
     * @return string
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * Set phone
     * @param string
     * @return User
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;
        return $this;
    }

    /**
     * Get phone
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * Set fax
     * @param string
     * @return User
     */
    public function setFax($fax)
    {
        $this->fax = $fax;
        return $this;
    }

    /**
     * Get fax
     * @return string
     */
    public function getFax()
    {
        return $this->fax;
    }
}