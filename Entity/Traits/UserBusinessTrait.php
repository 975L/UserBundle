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
use c975L\UserBundle\Entity\UserLight;

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
     * @ORM\Column(name="business_phone", type="phone_number", nullable=true)
     */
    private $businessPhone;

    /**
     * Fax number for the Business
     * @var string
     *
     * @AssertPhoneNumber
     * @ORM\Column(name="business_fax", type="phone_number", nullable=true)
     */
    private $businessFax;


//GETTERS/SETTERS
    /**
     * Set businessType
     * @param string $businessType Business type
     * @return UserLight
     */
    public function setBusinessType(?string $businessType)
    {
        $this->businessType = $businessType;
        return $this;
    }

    /**
     * Get businessType
     * @return string
     */
    public function getBusinessType(): ?string
    {
        return $this->businessType;
    }

    /**
     * Set businessName
     * @param string $businessName Business name
     * @return UserLight
     */
    public function setBusinessName(?string $businessName)
    {
        $this->businessName = $businessName;
        return $this;
    }

    /**
     * Get businessName
     * @return string
     */
    public function getBusinessName(): ?string
    {
        return $this->businessName;
    }

    /**
     * Set businessAddress
     * @param string $businessAddress Business address
     * @return UserLight
     */
    public function setBusinessAddress(?string $businessAddress)
    {
        $this->businessAddress = $businessAddress;
        return $this;
    }

    /**
     * Get businessAddress
     * @return string
     */
    public function getBusinessAddress(): ?string
    {
        return $this->businessAddress;
    }

    /**
     * Set businessAddress2
     * @param string $businessAddress2 Business Address 2
     * @return UserLight
     */
    public function setBusinessAddress2(?string $businessAddress2)
    {
        $this->businessAddress2 = $businessAddress2;
        return $this;
    }

    /**
     * Get businessAddress2
     * @return string
     */
    public function getBusinessAddress2(): ?string
    {
        return $this->businessAddress2;
    }

    /**
     * Set businessPostal
     * @param string $businessPostal Business postal code
     * @return UserLight
     */
    public function setBusinessPostal(?string $businessPostal)
    {
        $this->businessPostal = $businessPostal;
        return $this;
    }

    /**
     * Get businessPostal
     * @return string
     */
    public function getBusinessPostal(): ?string
    {
        return $this->businessPostal;
    }

    /**
     * Set businessTown
     * @param string $businessTown Business town
     * @return UserLight
     */
    public function setBusinessTown(?string $businessTown)
    {
        $this->businessTown = $businessTown;
        return $this;
    }

    /**
     * Get businessTown
     * @return string
     */
    public function getBusinessTown(): ?string
    {
        return $this->businessTown;
    }

    /**
     * Set businessCountry
     * @param string $businessCountry Business country
     * @return UserLight
     */
    public function setBusinessCountry(?string $businessCountry)
    {
        $this->businessCountry = $businessCountry;
        return $this;
    }

    /**
     * Get businessCountry
     * @return string
     */
    public function getBusinessCountry(): ?string
    {
        return $this->businessCountry;
    }

    /**
     * Set businessSiret
     * @param string $businessSiret Business siret number
     * @return UserLight
     */
    public function setBusinessSiret(?string $businessSiret)
    {
        $this->businessSiret = str_replace(array(' ', '.', '-', ',', ', '), '', trim($businessSiret));
        return $this;
    }

    /**
     * Get businessSiret
     * @return string
     */
    public function getBusinessSiret(): ?string
    {
        return $this->businessSiret;
    }

    /**
     * Set businessVat
     * @param string $businessVat Business vat number
     * @return UserLight
     */
    public function setBusinessVat(?string $businessVat)
    {
        $this->businessVat = str_replace(array(' ', '.', '-', ',', ', '), '', trim(strtoupper($businessVat)));
        return $this;
    }

    /**
     * Get businessVat
     * @return string
     */
    public function getBusinessVat(): ?string
    {
        return $this->businessVat;
    }

    /**
     * Set businessPhone
     * @param string $businessPhone Business phone
     * @return UserLight
     */
    public function setBusinessPhone(?string $businessPhone)
    {
        $this->businessPhone = $businessPhone;
        return $this;
    }

    /**
     * Get businessPhone
     * @return object|null
     */
    public function getBusinessPhone()
    {
        return $this->businessPhone;
    }

    /**
     * Set businessFax
     * @param string $businessFax Business fax
     * @return UserLight
     */
    public function setBusinessFax(?string $businessFax)
    {
        $this->businessFax = $businessFax;
        return $this;
    }

    /**
     * Get businessFax
     * @return object|null
     */
    public function getBusinessFax()
    {
        return $this->businessFax;
    }
}