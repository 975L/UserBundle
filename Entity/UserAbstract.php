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
use c975L\UserBundle\Entity\UserLightAbstract;
use c975L\UserBundle\Validator\Constraints as UserBundleAssert;
use c975L\UserBundle\Validator\Constraints\UserChallenge;

/**
 * Entity UserAbstract
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 * @copyright 2018 975L <contact@975l.com>
 *
 * @ORM\MappedSuperclass
 */
abstract class UserAbstract extends UserLightAbstract
{
    /**
     * Gender for the user
     * @var string
     *
     * @ORM\Column(name="gender", type="string")
     * @Assert\Choice(
     *      choices = {"woman", "man"},
     *      message = "gender.choose_valid"
     * )
     */
    private $gender;

    /**
     * Firstname for the user
     * @var string
     *
     * @ORM\Column(name="firstname", length=48, type="string")
     * @Assert\Length(
     *      min = 2,
     *      max = 48,
     *      minMessage = "firstname.min_length",
     *      maxMessage = "firstname.max_length"
     * )
     */
    private $firstname;

    /**
     * Lastname for the user
     * @var string
     *
     * @ORM\Column(name="lastname", length=48, type="string")
     * @Assert\Length(
     *      min = 2,
     *      max = 48,
     *      minMessage = "lastname.min_length",
     *      maxMessage = "lastname.max_length"
     * )
     */
    private $lastname;

    /**
     * url used for the Avatar for the user
     * @var string
     *
     * @ORM\Column(name="avatar", type="string", length=255, nullable=true)
     */
    private $avatar;

    /**
     * DateTime of latest sign-in
     * @var DateTime
     *
     * @ORM\Column(name="latest_signin", type="datetime", nullable=true)
     */
    private $latestSignin;

    /**
     * DateTime of latest sign-out
     * @var DateTime
     *
     * @ORM\Column(name="latest_signout", type="datetime", nullable=true)
     */
    private $latestSignout;

    /**
     * Locale for the user
     * @var string
     *
     * @ORM\Column(name="locale", type="string", nullable=true)
     */
    private $locale;

//ADDRESS
//Mapping is done in children classes
    /**
     * See property in UserAddressAbstract
     */
    private $address;

    /**
     * See property in UserAddressAbstract
     */
    private $address2;

    /**
     * See property in UserAddressAbstract
     */
    private $postal;

    /**
     * See property in UserAddressAbstract
     */
    private $town;

    /**
     * See property in UserAddressAbstract
     */
    private $country;

    /**
     * See property in UserAddressAbstract
     */
    private $phone;

    /**
     * See property in UserAddressAbstract
     */
    private $fax;

//BUSINESS
//Mapping is done in children classes
    /**
     * See property in UserBusinessAbstract
     */
    private $businessType;

    /**
     * See property in UserBusinessAbstract
     */
    private $businessName;

    /**
     * See property in UserBusinessAbstract
     */
    private $businessAddress;

    /**
     * See property in UserBusinessAbstract
     */
    private $businessAddress2;

    /**
     * See property in UserBusinessAbstract
     */
    private $businessPostal;

    /**
     * See property in UserBusinessAbstract
     */
    private $businessTown;

    /**
     * See property in UserBusinessAbstract
     */
    private $businessCountry;

    /**
     * See property in UserBusinessAbstract
     */
    private $businessSiret;

    /**
     * See property in UserBusinessAbstract
     */
    private $businessVat;

    /**
     * See property in UserBusinessAbstract
     */
    private $businessPhone;

    /**
     * See property in UserBusinessAbstract
     */
    private $businessFax;

//SOCIAL
//Mapping is done in children classes
    /**
     * See property in UserSocialAbstract
     */
    private $socialNetwork;

    /**
     * See property in UserSocialAbstract
     */
    private $socialId;

    /**
     * See property in UserSocialAbstract
     */
    private $socialToken;

    /**
     * See property in UserSocialAbstract
     */
    private $socialPicture;


//GETTERS/SETTERS
    /**
     * Set gender
     * @param string
     * @return User
     */
    public function setGender($gender)
    {
        $this->gender = $gender;
        return $this;
    }

    /**
     * Get gender
     * @return string
     */
    public function getGender()
    {
        return $this->gender;
    }

    /**
     * Set firstname
     * @param string
     * @return User
     */
    public function setFirstname($firstname)
    {
        $this->firstname = $firstname;
        return $this;
    }

    /**
     * Get firstname
     * @return string
     */
    public function getFirstname()
    {
        return $this->firstname;
    }

    /**
     * Set lastname
     * @param string
     * @return User
     */
    public function setLastname($lastname)
    {
        $this->lastname = $lastname;
        return $this;
    }

    /**
     * Get lastname
     * @return string
     */
    public function getLastname()
    {
        return $this->lastname;
    }

    /**
     * Set avatar
     * @param string
     * @return User
     */
    public function setAvatar($avatar)
    {
        $this->avatar = $avatar;
        return $this;
    }

    /**
     * Get avatar
     * @return string
     */
    public function getAvatar()
    {
        return $this->avatar;
    }

    /**
     * Set latestSignin
     * @param DateTime
     * @return User
     */
    public function setLatestSignin($latestSignin)
    {
        $this->latestSignin = $latestSignin;
        return $this;
    }

    /**
     * Get latestSignin
     * @return \DateTime
     */
    public function getLatestSignin()
    {
        return $this->latestSignin;
    }

    /**
     * Set latestSignout
     * @param \DateTime
     * @return User
     */
    public function setLatestSignout($latestSignout)
    {
        $this->latestSignout = $latestSignout;
        return $this;
    }

    /**
     * Get latestSignout
     * @return \DateTime
     */
    public function getLatestSignout()
    {
        return $this->latestSignout;
    }

    /**
     * Set locale
     * @param string
     * @return User
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;
        return $this;
    }

    /**
     * Get locale
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

//GETTERS/SETTERS ADDRESS
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

//GETTERS/SETTERS BUSINESS
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

//GETTERS/SETTERS SOCIAL
    /**
     * Set socialNetwork
     * @param string
     * @return User
     */
    public function setSocialNetwork($socialNetwork)
    {
        $this->socialNetwork = $socialNetwork;
        return $this;
    }

    /**
     * Get socialNetwork
     * @return string
     */
    public function getSocialNetwork()
    {
        return $this->socialNetwork;
    }

    /**
     * Set socialId
     * @param string
     * @return User
     */
    public function setSocialId($socialId)
    {
        $this->socialId = $socialId;
        return $this;
    }

    /**
     * Get socialId
     * @return string
     */
    public function getSocialId()
    {
        return $this->socialId;
    }

    /**
     * Set socialToken
     * @param string
     * @return User
     */
    public function setSocialToken($socialToken)
    {
        $this->socialToken = $socialToken;
        return $this;
    }

    /**
     * Get socialToken
     * @return string
     */
    public function getSocialToken()
    {
        return $this->socialToken;
    }

    /**
     * Set socialPicture
     * @param string
     * @return User
     */
    public function setSocialPicture($socialPicture)
    {
        $this->socialPicture = $socialPicture;
        return $this;
    }

    /**
     * Get socialPicture
     * @return string
     */
    public function getSocialPicture()
    {
        return $this->socialPicture;
    }
}
