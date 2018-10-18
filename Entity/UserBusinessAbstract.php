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
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Misd\PhoneNumberBundle\Validator\Constraints\PhoneNumber as AssertPhoneNumber;
use c975L\UserBundle\Validator\Constraints as c975LUserBundleAssert;
use c975L\UserBundle\Validator\Constraints\Siret;
use c975L\UserBundle\Validator\Constraints\Tva;
use c975L\UserBundle\Entity\UserAbstract;

/**
 * Entity UserBusinessAbstract
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 * @copyright 2018 975L <contact@975l.com>
 */
abstract class UserBusinessAbstract extends UserAbstract
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
    protected $businessType;

    /**
     * Name for the Business
     * @var string
     *
     * @Assert\Regex(
     *      pattern="/^([0-9a-zA-Z\#\.\_\-\ \*]{0,36})$/i"
     * )
     * @ORM\Column(name="business_name", type="string", nullable=true)
     */
    protected $businessName;

    /**
     * Address for the Business
     * @var string
     *
     * @ORM\Column(name="business_address", type="string", nullable=true)
     */
    protected $businessAddress;

    /**
     * Second line for the address for the Business
     * @var string
     *
     * @ORM\Column(name="business_address2", type="string", nullable=true)
     */
    protected $businessAddress2;

    /**
     * Postal code for the Business
     * @var string
     *
     * @ORM\Column(name="business_postal", type="string", nullable=true)
     */
    protected $businessPostal;

    /**
     * Town for the Business
     * @var string
     *
     * @ORM\Column(name="business_town", type="string", nullable=true)
     */
    protected $businessTown;

    /**
     * Country for the Business
     * @var string
     *
     * @ORM\Column(name="business_country", type="string", nullable=true)
     */
    protected $businessCountry;

    /**
     * Siret for the Business
     * @var string
     *
     * @c975LUserBundleAssert\Siret(
     *      message = "siret.not_valid"
     * )
     * @ORM\Column(name="business_siret", type="string", length=14, nullable=true)
     */
    protected $businessSiret;

    /**
     * TVA number for the Business
     * @var string
     *
     * @c975LUserBundleAssert\Vat(
     *      message = "vat.not_valid"
     * )
     * @ORM\Column(name="business_tva", type="string", length=13, nullable=true)
     */
    protected $businessVat;

    /**
     * Phone number for the Business
     * @var string
     *
     * @AssertPhoneNumber
     * @ORM\Column(type="phone_number")
     * @ORM\Column(name="business_phone", type="string", nullable=true)
     */
    protected $businessPhone;

    /**
     * Fax number for the Business
     * @var string
     *
     * @AssertPhoneNumber
     * @ORM\Column(type="phone_number")
     * @ORM\Column(name="business_fax", type="string", nullable=true)
     */
    protected $businessFax;
}