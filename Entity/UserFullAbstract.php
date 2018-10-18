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
 * Entity UserFullAbstract
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 * @copyright 2018 975L <contact@975l.com>
 */
abstract class UserFullAbstract extends UserAbstract
{
//ADDRESS
    /**
     * See property in UserAddressAbstract
     *
     * @ORM\Column(name="address", type="string", nullable=true)
     */
    private $address;

    /**
     * See property in UserAddressAbstract
     *
     * @ORM\Column(name="address2", type="string", nullable=true)
     */
    private $address2;

    /**
     * See property in UserAddressAbstract
     *
     * @ORM\Column(name="postal", type="string", nullable=true)
     */
    private $postal;

    /**
     * See property in UserAddressAbstract
     *
     * @ORM\Column(name="town", type="string", nullable=true)
     */
    private $town;

    /**
     * See property in UserAddressAbstract
     *
     * @ORM\Column(name="country", type="string", nullable=true)
     */
    private $country;

    /**
     * See property in UserAddressAbstract
     *
     * @AssertPhoneNumber
     * @ORM\Column(type="phone_number")
     * @ORM\Column(name="phone", type="string", nullable=true)
     */
    private $phone;

    /**
     * See property in UserAddressAbstract
     *
     * @AssertPhoneNumber
     * @ORM\Column(type="phone_number")
     * @ORM\Column(name="fax", type="string", nullable=true)
     */
    private $fax;

//BUSINESS
    /**
     * See property in UserBusinessAbstract
     *
     * @Assert\Regex(
     *      pattern="/^(association|business|individual)$/i"
     * )
     * @ORM\Column(name="business_type", type="string", nullable=true)
     */
    private $businessType;

    /**
     * See property in UserBusinessAbstract
     *
     * @Assert\Regex(
     *      pattern="/^([0-9a-zA-Z\#\.\_\-\ \*]{0,36})$/i"
     * )
     * @ORM\Column(name="business_name", type="string", nullable=true)
     */
    private $businessName;

    /**
     * See property in UserBusinessAbstract
     *
     * @ORM\Column(name="business_address", type="string", nullable=true)
     */
    private $businessAddress;

    /**
     * See property in UserBusinessAbstract
     *
     * @ORM\Column(name="business_address2", type="string", nullable=true)
     */
    private $businessAddress2;

    /**
     * See property in UserBusinessAbstract
     *
     * @ORM\Column(name="business_postal", type="string", nullable=true)
     */
    private $businessPostal;

    /**
     * See property in UserBusinessAbstract
     *
     * @ORM\Column(name="business_town", type="string", nullable=true)
     */
    private $businessTown;

    /**
     * See property in UserBusinessAbstract
     *
     * @ORM\Column(name="business_country", type="string", nullable=true)
     */
    private $businessCountry;

    /**
     * See property in UserBusinessAbstract
     *
     * @c975LUserBundleAssert\Siret(
     *      message = "siret.not_valid"
     * )
     * @ORM\Column(name="business_siret", type="string", length=14, nullable=true)
     */
    private $businessSiret;

    /**
     * See property in UserBusinessAbstract
     *
     * @c975LUserBundleAssert\Vat(
     *      message = "vat.not_valid"
     * )
     * @ORM\Column(name="business_tva", type="string", length=13, nullable=true)
     */
    private $businessVat;

    /**
     * See property in UserBusinessAbstract
     *
     * @AssertPhoneNumber
     * @ORM\Column(type="phone_number")
     * @ORM\Column(name="business_phone", type="string", nullable=true)
     */
    private $businessPhone;

    /**
     * See property in UserBusinessAbstract
     *
     * @AssertPhoneNumber
     * @ORM\Column(type="phone_number")
     * @ORM\Column(name="business_fax", type="string", nullable=true)
     */
    private $businessFax;

//SOCIAL
    /**
     * See property in UserSocialAbstract
     *
     * @ORM\Column(name="social_network", type="string", nullable=true)
     */
    private $socialNetwork;

    /**
     * See property in UserSocialAbstract
     *
     * @ORM\Column(name="social_id", type="string", length=255, nullable=true)
     */
    private $socialId;

    /**
     * See property in UserSocialAbstract
     *
     * @ORM\Column(name="social_token", type="string", length=255, nullable=true)
     */
    private $socialToken;

    /**
     * See property in UserSocialAbstract
     *
     * @ORM\Column(name="social_picture", type="string", length=255, nullable=true)
     */
    private $socialPicture;
}
