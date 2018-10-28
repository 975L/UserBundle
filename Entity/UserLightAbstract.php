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
 * Entity UserLightAbstract
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 * @copyright 2018 975L <contact@975l.com>
 *
 * @ORM\MappedSuperclass
 */
abstract class UserLightAbstract implements AdvancedUserInterface
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
    private $id;

    /**
     * If the user allows use of its account (GDPR)
     * @var bool
     *
     * @ORM\Column(name="allow_use", type="boolean")
     */
    private $allowUse;

    /**
     * Unique user identifier
     * @var string
     *
     * @ORM\Column(type="string", length=32, unique=true)
     */
    private $identifier;

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
    private $email;

    /**
     * DateTime for the creation
     * @var DateTime
     *
     * @ORM\Column(name="creation", type="datetime", nullable=true)
     */
    private $creation;

    /**
     * If account is enabled
     * @var bool
     *
     * @ORM\Column(name="enabled", type="boolean")
     */
    private $enabled;

    /**
     * Salt used to hash the password
     * @var string
     *
     * @ORM\Column(name="salt", length=255, type="string")
     */
    private $salt;

    /**
     * Password hashed
     * @var string
     *
     * @ORM\Column(name="password", length=255, type="string")
     */
    private $password;

    /**
     * Token used for sign-up and password recovery
     * @var string
     *
     * @ORM\Column(name="token", length=40, type="string")
     */
    private $token;

    /**
     * DateTime of request for password recovery
     * @var DateTime
     *
     * @ORM\Column(name="password_request", type="datetime")
     */
    private $passwordRequest;

    /**
     * Roles for the user
     * @var string
     *
     * @ORM\Column(name="roles", type="string")
     */
    private $roles;

    /**
     * Plain password (not strored, used only at sign-up and password chnage times)
     * @var string
     *
     * @Assert\Regex(
     *      pattern="/(?=.*[A-Za-z])(?=.*[@#$%^*])(?=.*[0-9]).{8,48}/",
     *      message="password.requirement"
     * )
     */
    private $plainPassword;

    /**
     * Answer to the proposed challenge to avoid bots
     * @var string
     *
     * @UserBundleAssert\UserChallenge(
     *      message = "label.error_challenge"
     * )
     */
    private $challenge;

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

//CONVERT TO ARRAY
    /**
     * Converts the entity in an array
     */
    public function toArray()
    {
        return get_object_vars($this);
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
        $roles = explode(',', $this->roles);

        //Adds default role
        $roles[] = static::ROLE_DEFAULT;

        return array_unique($roles);
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
}
