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
use Symfony\Component\Validator\Constraints as Assert;
use c975L\UserBundle\Validator\Constraints as c975LUserBundleAssert;
use c975L\UserBundle\Entity\UserLight;

/**
 * Trait UserAddressTrait
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 * @copyright 2018 975L <contact@975l.com>
 */
trait UserLightTrait
{
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
     * @c975LUserBundleAssert\UserChallenge(
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
     * Serializes the user
     * @return string
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
     * Unserialize the user
     * @return array
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
     * Check if the user has the specified role
     * @return bool
     */
    public function hasRole($role)
    {
        return in_array(strtoupper($role), $this->getRoles(), true);
    }

    /**
     * Adds the role to the user
     * @return UserLight
     */
    public function addRole($role)
    {
        $role = strtoupper(trim($role));
        if ('ROLE_' !== substr($role, 0, 5)) {
            $role = 'ROLE_' . $role;
        }

        if ($role === static::ROLE_DEFAULT) {
            return $this;
        }

        if (!in_array($role, $this->getRoles(), true)) {
            if (null === $this->roles) {
                $this->roles = trim($role);
            } else {
                $this->roles .= ',' . trim($role);
            }
        }

        $this->setRoles($this->roles);

        return $this;
    }

    /**
     * Deletes the role to the user
     * @return UserLight
     */
    public function deleteRole($role)
    {
        $role = strtoupper($role);
        if ('ROLE_' !== substr($role, 0, 5)) {
            $role = 'ROLE_' . $role;
        }

        if ($role === static::ROLE_DEFAULT) {
            return $this;
        }

        if (in_array($role, $this->getRoles(), true)) {
            $this->roles = str_ireplace($role, '', $this->roles);
        }

        $this->roles = empty($this->roles) ? null : $this->roles;
        $this->setRoles($this->roles);

        return $this;
    }

    /**
     * Set roles
     * @param string
     * @return UserLight
     */
    public function setRoles($roles)
    {
        if (is_array($roles)) {
            array_unique($roles);
            unset($roles[static::ROLE_DEFAULT]);
            sort($roles);

            $this->roles = implode(',', $roles);
        }

        return $this;
    }

    /**
     * Get roles
     * @return array
     */
    public function getRoles()
    {
        $roles = array_map('trim', explode(',', $this->roles));
        $roles = array_map('strtoupper', $roles);

        //Adds default role
        if (!in_array(static::ROLE_DEFAULT, $roles, true)) {
            $roles[] = static::ROLE_DEFAULT;
        }

        $roles = array_filter($roles);
        array_unique($roles);
        sort($roles);

        return $roles;
    }

//CONVERT TO ARRAY
    /**
     * Converts the entity in an array
     * @return array
     */
    public function toArray()
    {
        $userArray = get_object_vars($this);

        //Unsets unneeded data
        $unNeededData = array (
            'id',
            'salt',
            'password',
            'token',
            'passwordRequest',
            'plainPassword',
            'challenge',
        );
        foreach ($unNeededData as $data) {
            unset($userArray[$data]);
        }

        return $userArray;
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
     * @return UserLight
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
     * @return UserLight
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
     * @return UserLight
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
     * @return UserLight
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
     * @return UserLight
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
     * @return UserLight
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
     * @return UserLight
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
     * @return UserLight
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
     * @return UserLight
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
     * Set plainPassword
     * @param string
     * @return UserLight
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
     * @return UserLight
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