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
use Symfony\Component\Validator\Constraints as Assert;
use c975L\UserBundle\Validator\Constraints as c975LUserBundleAssert;

/**
 * Trait UserBusinessTrait
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 * @copyright 2018 975L <contact@975l.com>
 */
trait UserBusinessTrait
{
    /**
     * Type of bussiness
     * @var string
     *
     * @Assert\Regex(
     *      pattern="/^(association|business|individual)$/i"
     * )
     * @ORM\Column(name="business_type", type="string", nullable=true)
     */
    private $businessType;

    /**
     * Name for the Business
     * @var string
     *
     * @Assert\Regex(
     *      pattern="/^([0-9a-zA-Z\#\.\_\-\ \*]{0,36})$/i"
     * )
     * @ORM\Column(name="business_name", type="string", nullable=true)
     */
    private $businessName;

    /**
     * Address for the Business
     * @var string
     *
     * @ORM\Column(name="business_address", type="string", nullable=true)
     */
    private $businessAddress;

    /**
     * Second line for the address for the Business
     * @var string
     *
     * @ORM\Column(name="business_address2", type="string", nullable=true)
     */
    private $businessAddress2;

    /**
     * Postal code for the Business
     * @var string
     *
     * @ORM\Column(name="business_postal", type="string", nullable=true)
     */
    private $businessPostal;

    /**
     * Town for the Business
     * @var string
     *
     * @ORM\Column(name="business_town", type="string", nullable=true)
     */
    private $businessTown;

    /**
     * Country for the Business
     * @var string
     *
     * @ORM\Column(name="business_country", type="string", nullable=true)
     */
    private $businessCountry;

    /**
     * Siret for the Business
     * @var string
     *
     * @c975LUserBundleAssert\Siret(
     *      message = "siret.not_valid"
     * )
     * @ORM\Column(name="business_siret", type="string", length=14, nullable=true)
     */
    private $businessSiret;

    /**
     * TVA number for the Business
     * @var string
     *
     * @c975LUserBundleAssert\Vat(
     *      message = "vat.not_valid"
     * )
     * @ORM\Column(name="business_tva", type="string", length=13, nullable=true)
     */
    private $businessVat;

    /**
     * Phone number for the Business
     * @var string
     *
     * @AssertPhoneNumber
     * @ORM\Column(type="phone_number")
     * @ORM\Column(name="business_phone", type="string", nullable=true)
     */
    private $businessPhone;

    /**
     * Fax number for the Business
     * @var string
     *
     * @AssertPhoneNumber
     * @ORM\Column(type="phone_number")
     * @ORM\Column(name="business_fax", type="string", nullable=true)
     */
    private $businessFax;


//GETTERS/SETTERS
    /**
     * Set businessType
     * @param string
     * @return User
     */
    public function setBusinessType($businessType)
    {
        $this->businessType = $businessType;
        return $this;
    }

    /**
     * Get businessType
     * @return string
     */
    public function getBusinessType()
    {
        return $this->businessType;
    }

    /**
     * Set businessName
     * @param string
     * @return User
     */
    public function setBusinessName($businessName)
    {
        $this->businessName = $businessName;
        return $this;
    }

    /**
     * Get businessName
     * @return string
     */
    public function getBusinessName()
    {
        return $this->businessName;
    }

    /**
     * Set businessAddress
     * @param string
     * @return User
     */
    public function setBusinessAddress($businessAddress)
    {
        $this->businessAddress = $businessAddress;
        return $this;
    }

    /**
     * Get businessAddress
     * @return string
     */
    public function getBusinessAddress()
    {
        return $this->businessAddress;
    }

    /**
     * Set businessAddress2
     * @param string
     * @return User
     */
    public function setBusinessAddress2($businessAddress2)
    {
        $this->businessAddress2 = $businessAddress2;
        return $this;
    }

    /**
     * Get businessAddress2
     * @return string
     */
    public function getBusinessAddress2()
    {
        return $this->businessAddress2;
    }

    /**
     * Set businessPostal
     * @param string
     * @return User
     */
    public function setBusinessPostal($businessPostal)
    {
        $this->businessPostal = $businessPostal;
        return $this;
    }

    /**
     * Get businessPostal
     * @return string
     */
    public function getBusinessPostal()
    {
        return $this->businessPostal;
    }

    /**
     * Set businessTown
     * @param string
     * @return User
     */
    public function setBusinessTown($businessTown)
    {
        $this->businessTown = $businessTown;
        return $this;
    }

    /**
     * Get businessTown
     * @return string
     */
    public function getBusinessTown()
    {
        return $this->businessTown;
    }

    /**
     * Set businessCountry
     * @param string
     * @return User
     */
    public function setBusinessCountry($businessCountry)
    {
        $this->businessCountry = $businessCountry;
        return $this;
    }

    /**
     * Get businessCountry
     * @return string
     */
    public function getBusinessCountry()
    {
        return $this->businessCountry;
    }

    /**
     * Set businessSiret
     * @param string
     * @return User
     */
    public function setBusinessSiret($businessSiret)
    {
        $this->businessSiret = str_replace(array(' ', '.', '-', ',', ', '), '', trim($businessSiret));
        return $this;
    }

    /**
     * Get businessSiret
     * @return string
     */
    public function getBusinessSiret()
    {
        return $this->businessSiret;
    }

    /**
     * Set businessVat
     * @param string
     * @return User
     */
    public function setBusinessVat($businessVat)
    {
        $this->businessVat = str_replace(array(' ', '.', '-', ',', ', '), '', trim(strtoupper($businessVat)));
        return $this;
    }

    /**
     * Get businessVat
     * @return string
     */
    public function getBusinessVat()
    {
        return $this->businessVat;
    }

    /**
     * Set businessPhone
     * @param string
     * @return User
     */
    public function setBusinessPhone($businessPhone)
    {
        $this->businessPhone = $businessPhone;
        return $this;
    }

    /**
     * Get businessPhone
     * @return string
     */
    public function getBusinessPhone()
    {
        return $this->businessPhone;
    }

    /**
     * Set businessFax
     * @param string
     * @return User
     */
    public function setBusinessFax($businessFax)
    {
        $this->businessFax = $businessFax;
        return $this;
    }

    /**
     * Get businessFax
     * @return string
     */
    public function getBusinessFax()
    {
        return $this->businessFax;
    }
}