<?php
/*
 * (c) 2018: 975L <contact@975l.com>
 * (c) 2018: Laurent Marquet <laurent.marquet@laposte.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace c975L\UserBundle\Entity\Traits;

use c975L\UserBundle\Entity\UserLight;
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
     * @param string $address Address
     * @return UserLight
     */
    public function setAddress(?string $address)
    {
        $this->address = $address;
        return $this;
    }

    /**
     * Get address
     * @return string
     */
    public function getAddress(): ?string
    {
        return $this->address;
    }

    /**
     * Set address2
     * @param string $address2 Address 2
     * @return UserLight
     */
    public function setAddress2(?string $address2)
    {
        $this->address2 = $address2;
        return $this;
    }

    /**
     * Get address2
     * @return string
     */
    public function getAddress2(): ?string
    {
        return $this->address2;
    }

    /**
     * Set postal
     * @param string $postal Postal code
     * @return UserLight
     */
    public function setPostal(?string $postal)
    {
        $this->postal = $postal;
        return $this;
    }

    /**
     * Get postal
     * @return string
     */
    public function getPostal(): ?string
    {
        return $this->postal;
    }

    /**
     * Set town
     * @param string $town Town
     * @return UserLight
     */
    public function setTown(?string $town)
    {
        $this->town = $town;
        return $this;
    }

    /**
     * Get town
     * @return string
     */
    public function getTown(): ?string
    {
        return $this->town;
    }

    /**
     * Set country
     * @param string $country Country
     * @return UserLight
     */
    public function setCountry(?string $country)
    {
        $this->country = $country;
        return $this;
    }

    /**
     * Get country
     * @return string
     */
    public function getCountry(): ?string
    {
        return $this->country;
    }

    /**
     * Set phone
     * @param string $phone Phone
     * @return UserLight
     */
    public function setPhone(?string $phone)
    {
        $this->phone = $phone;
        return $this;
    }

    /**
     * Get phone
     * @return string
     */
    public function getPhone(): ?string
    {
        return $this->phone;
    }

    /**
     * Set fax
     * @param string $fax Fax
     * @return UserLight
     */
    public function setFax(?string $fax)
    {
        $this->fax = $fax;
        return $this;
    }

    /**
     * Get fax
     * @return string
     */
    public function getFax(): ?string
    {
        return $this->fax;
    }
}