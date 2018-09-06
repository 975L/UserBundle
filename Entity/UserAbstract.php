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
use Symfony\Component\Security\Core\User\AdvancedUserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use c975L\UserBundle\Validator\Constraints as UserBundleAssert;
use c975L\UserBundle\Validator\Constraints\UserChallenge;

/**
 * Entity UserAbstract
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 * @copyright 2018 975L <contact@975l.com>
 *
 * @ORM\MappedSuperclass
 */
abstract class UserAbstract implements AdvancedUserInterface
{
    const ROLE_DEFAULT = 'ROLE_USER';
    const ROLE_SUPER_ADMIN = 'ROLE_SUPER_ADMIN';

    /**
     * User unique id
     * @var int
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(name="id", type="integer")
     */
    protected $id;

    /**
     * If the user allows use of its account (GDPR)
     * @var bool
     *
     * @ORM\Column(name="allow_use", type="boolean")
     */
    protected $allowUse;

    /**
     * Unique user identifier
     * @var string
     *
     * @ORM\Column(type="string", length=32, unique=true)
     */
    protected $identifier;

    /**
     * Email for the user
     * @var string
     *
     * @Assert\NotBlank()
     * @ORM\Column(type="string", length=128, unique=true)
     * @Assert\Email(
     *     message = "email.not_valid",
     *     checkMX = true
     * )
     */
    protected $email;

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
    protected $gender;

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
    protected $firstname;

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
    protected $lastname;

    /**
     * DateTime for the creation
     * @var DateTime
     *
     * @ORM\Column(name="creation", type="datetime", nullable=true)
     */
    protected $creation;

    /**
     * url used for the Avatar for the user
     * @var string
     *
     * @ORM\Column(name="avatar", type="string", length=255, nullable=true)
     */
    protected $avatar;

    /**
     * If account is enabled
     * @var bool
     *
     * @ORM\Column(name="enabled", type="boolean")
     */
    protected $enabled;

    /**
     * Salt used to hash the password
     * @var string
     *
     * @ORM\Column(name="salt", length=255, type="string")
     */
    protected $salt;

    /**
     * Password hashed
     * @var string
     *
     * @ORM\Column(name="password", length=255, type="string")
     */
    protected $password;

    /**
     * DateTime of latest sign-in
     * @var DateTime
     *
     * @ORM\Column(name="latest_signin", type="datetime", nullable=true)
     */
    protected $latestSignin;

    /**
     * DateTime of latest sign-out
     * @var DateTime
     *
     * @ORM\Column(name="latest_signout", type="datetime", nullable=true)
     */
    protected $latestSignout;

    /**
     * Token used for sign-up and password recovery
     * @var string
     *
     * @ORM\Column(name="token", length=40, type="string")
     */
    protected $token;

    /**
     * DateTime of request for password recovery
     * @var DateTime
     *
     * @ORM\Column(name="password_request", type="datetime")
     */
    protected $passwordRequest;

    /**
     * Roles for the user
     * @var string
     *
     * @ORM\Column(name="roles", type="string")
     */
    protected $roles;

    /**
     * Locale for the user
     * @var string
     *
     * @ORM\Column(name="locale", type="string", nullable=true)
     */
    protected $locale;

    /**
     * Plain password (not strored, used only at sign-up and password chnage times)
     * @var string
     *
     * @Assert\Regex(
     *      pattern="/(?=.*[A-Za-z])(?=.*[@#$%^*])(?=.*[0-9]).{8,48}/",
     *      message="password.requirement"
     * )
     */
    protected $plainPassword;

    /**
     * Answer to the proposed challenge to avoid bots
     * @var string
     *
     * @UserBundleAssert\UserChallenge(
     *      message = "label.error_challenge"
     * )
     */
    protected $challenge;

//ADDRESS
//Mapping is done in children classes
    /**
     * See property in UserAddressAbstract
     */
    protected $address;

    /**
     * See property in UserAddressAbstract
     */
    protected $address2;

    /**
     * See property in UserAddressAbstract
     */
    protected $postal;

    /**
     * See property in UserAddressAbstract
     */
    protected $town;

    /**
     * See property in UserAddressAbstract
     */
    protected $country;

    /**
     * See property in UserAddressAbstract
     */
    protected $phone;

    /**
     * See property in UserAddressAbstract
     */
    protected $fax;

//BUSINESS
//Mapping is done in children classes
    /**
     * See property in UserBusinessAbstract
     */
    protected $businessType;

    /**
     * See property in UserBusinessAbstract
     */
    protected $businessName;

    /**
     * See property in UserBusinessAbstract
     */
    protected $businessAddress;

    /**
     * See property in UserBusinessAbstract
     */
    protected $businessAddress2;

    /**
     * See property in UserBusinessAbstract
     */
    protected $businessPostal;

    /**
     * See property in UserBusinessAbstract
     */
    protected $businessTown;

    /**
     * See property in UserBusinessAbstract
     */
    protected $businessCountry;

    /**
     * See property in UserBusinessAbstract
     */
    protected $businessSiret;

    /**
     * See property in UserBusinessAbstract
     */
    protected $businessTva;

    /**
     * See property in UserBusinessAbstract
     */
    protected $businessPhone;

    /**
     * See property in UserBusinessAbstract
     */
    protected $businessFax;

//SOCIAL
//Mapping is done in children classes
    /**
     * See property in UserSocialAbstract
     */
    protected $socialNetwork;

    /**
     * See property in UserSocialAbstract
     */
    protected $socialId;

    /**
     * See property in UserSocialAbstract
     */
    protected $socialToken;

    /**
     * See property in UserSocialAbstract
     */
    protected $socialPicture;


//METHODS REQUESTED BY AdvancedUserInterface
    /**
     * {@inheritdoc}
     */
    public function eraseCredentials()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function isAccountNonExpired()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isAccountNonLocked()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isCredentialsNonExpired()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * {@inheritdoc}
     */
    public function serialize()
    {
        return serialize(array(
            $this->id,
            $this->password,
            $this->salt,
            $this->enabled,
            $this->email,
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function unserialize($serialized)
    {
        list (
            $this->id,
            $this->password,
            $this->salt,
            $this->enabled,
            $this->email,
        ) = unserialize($serialized);
    }

//ROLES
    /**
     * {@inheritdoc}
     */
    public function hasRole($role)
    {
        return in_array(strtoupper($role), $this->getRoles(), true);
    }

    /**
     * {@inheritdoc}
     */
    public function addRole($role)
    {
        $role = strtoupper($role);
        if ($role === static::ROLE_DEFAULT) {
            return $this;
        }

        if (!in_array($role, $this->roles, true)) {
            $this->roles[] = $role;
        }
        return $this;
    }

//GETTERS/SETTERS
    /**
     * Get id
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get username
     * Kept for compatibility, returns email value as specified in Symfony docs
     * @return string
     */
    public function getUsername()
    {
        return strtolower($this->email);
    }

    /**
     * Set allowUse
     * @param string
     * @return User
     */
    public function setAllowUse($allowUse)
    {
        $this->allowUse = (bool) $allowUse;
        return $this;
    }

    /**
     * Get allowUse
     * @return bool
     */
    public function getAllowUse()
    {
        return $this->allowUse;
    }

    /**
     * Set identifier
     * @param string
     * @return User
     */
    public function setIdentifier($identifier)
    {
        $this->identifier = $identifier;
        return $this;
    }

    /**
     * Get identifier
     * @return string
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * Set email
     * @param string
     * @return User
     */
    public function setEmail($email)
    {
        $this->email = strtolower($email);
        return $this;
    }

    /**
     * Get email
     * @return string
     */
    public function getEmail()
    {
        return strtolower($this->email);
    }

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
     * Set creation
     * @param \DateTime
     * @return User
     */
    public function setCreation($creation)
    {
        $this->creation = $creation;
        return $this;
    }

    /**
     * Get creation
     * @return \DateTime
     */
    public function getCreation()
    {
        return $this->creation;
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
     * Set enabled
     * @param bool
     * @return User
     */
    public function setEnabled($enabled)
    {
        $this->enabled = (bool) $enabled;
        return $this;
    }

    /**
     * Get enabled
     * @return bool
     */
    public function getEnabled()
    {
        return $this->enabled;
    }

    /**
     * Set salt
     * @param string
     * @return User
     */
    public function setSalt($salt)
    {
        $this->salt = $salt;
        return $this;
    }

    /**
     * Get salt
     * @return string
     */
    public function getSalt()
    {
        return $this->salt;
    }

    /**
     * Set password
     * @param string
     * @return User
     */
    public function setPassword($password)
    {
        $this->password = $password;
        return $this;
    }

    /**
     * Get password
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
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
     * Set token
     * @param string
     * @return User
     */
    public function setToken($token)
    {
        $this->token = $token;
        return $this;
    }

    /**
     * Get token
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Set passwordRequest
     * @param \DateTime
     * @return User
     */
    public function setPasswordRequest($passwordRequest)
    {
        $this->passwordRequest = $passwordRequest;
        return $this;
    }

    /**
     * Get passwordRequest
     * @return \DateTime
     */
    public function getPasswordRequest()
    {
        return $this->passwordRequest;
    }

    /**
     * Set roles
     * @param string
     * @return User
     */
    public function setRoles($roles)
    {
        $this->roles = array();

        foreach ($roles as $role) {
            $this->addRole($role);
        }
        return $this;
    }

    /**
     * Get roles
     * @return string
     */
    public function getRoles()
    {
        $roles = unserialize($this->roles);

        //Adds default role
        $roles[] = static::ROLE_DEFAULT;

        return array_unique($roles);
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

    /**
     * Set plainPassword
     * @param string
     * @return User
     */
    public function setPlainPassword($plainPassword)
    {
        $this->plainPassword = $plainPassword;
        return $this;
    }

    /**
     * Get plainPassword
     * @return string
     */
    public function getPlainPassword()
    {
        return $this->plainPassword;
    }

    /**
     * Set challenge
     * @param string
     * @return User
     */
    public function setChallenge($challenge)
    {
        $this->challenge = $challenge;
        return $this;
    }

    /**
     * Get challenge
     * @return string
     */
    public function getChallenge()
    {
        return $this->challenge;
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
     * Set businessTva
     * @param string
     * @return User
     */
    public function setBusinessTva($businessTva)
    {
        $this->businessTva = str_replace(array(' ', '.', '-', ',', ', '), '', trim(strtoupper($businessTva)));
        return $this;
    }

    /**
     * Get businessTva
     * @return string
     */
    public function getBusinessTva()
    {
        return $this->businessTva;
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