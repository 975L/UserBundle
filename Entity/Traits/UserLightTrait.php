<?php
/*
 * (c) 2018: 975L <contact@975l.com>
 * (c) 2018: Laurent Marquet <laurent.marquet@laposte.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace c975L\UserBundle\Entity\Traits;

use DateTime;
use DateTimeInterface;
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
     *     message = "email.not_valid"
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
        return $this->enabled;
    }

    /**
     * {@inheritdoc}
     */
    public function isAccountNonLocked()
    {
        return $this->enabled;
    }

    /**
     * {@inheritdoc}
     */
    public function isCredentialsNonExpired()
    {
        return $this->enabled;
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
    public function hasRole(?string $role): bool
    {
        return in_array(strtoupper($role), $this->getRoles(), true);
    }

    /**
     * Adds the role to the user
     */
    public function addRole(?string $role)
    {
        $role = strtoupper(trim($role));
        if ('ROLE_' !== substr($role, 0, 5)) {
            $role = 'ROLE_' . $role;
        }

        if ($role === static::ROLE_DEFAULT) {
            return $this;
        }

        if (!in_array($role, $this->getRoles(), true)) {
            $this->roles = null === $this->roles ? trim($role) : $this->roles . ',' . trim($role);
        }

        $this->setRoles(explode(',', $this->roles));

        return $this;
    }

    /**
     * Deletes the role to the user
     */
    public function deleteRole(?string $role)
    {
        $role = strtoupper($role);
        if ('ROLE_' !== substr($role, 0, 5)) {
            $role = 'ROLE_' . $role;
        }

        if (static::ROLE_DEFAULT === $role) {
            return $this;
        }

        if (in_array($role, $this->getRoles(), true)) {
            $this->roles = str_ireplace($role, '', $this->roles);
        }

        $this->roles = empty($this->roles) ? null : $this->roles;
        $this->setRoles(explode(',', $this->roles));

        return $this;
    }

    /**
     * Set roles
     * @param string $roles Roles
     */
    public function setRoles(array $roles)
    {
        array_unique($roles);
        unset($roles[static::ROLE_DEFAULT]);
        sort($roles);

        $this->roles = '' === implode(',', $roles) ? null : implode(',', $roles);

        return $this;
    }

    /**
     * Get roles
     * @return array
     */
    public function getRoles(): array
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
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Get username
     * Kept for compatibility, returns email value as specified in Symfony docs
     * @return string
     */
    public function getUsername(): ?string
    {
        return strtolower($this->email);
    }

    /**
     * Set allowUse
     * @param bool $allowUse If use is allowed
     * @return UserLight
     */
    public function setAllowUse(bool $allowUse)
    {
        $this->allowUse = $allowUse;
        return $this;
    }

    /**
     * Get allowUse
     * @return bool
     */
    public function getAllowUse(): bool
    {
        return (bool) $this->allowUse;
    }

    /**
     * Set identifier
     * @param string $identifier Identifier
     * @return UserLight
     */
    public function setIdentifier(?string $identifier)
    {
        $this->identifier = $identifier;
        return $this;
    }

    /**
     * Get identifier
     * @return string
     */
    public function getIdentifier(): ?string
    {
        return $this->identifier;
    }

    /**
     * Set email
     * @param string $email Email
     * @return UserLight
     */
    public function setEmail(?string $email)
    {
        $this->email = $email;
        return $this;
    }

    /**
     * Get email
     * @return string
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * Set creation
     * @param DateTime $creation Datetime for creation
     * @return UserLight
     */
    public function setCreation(?DateTimeInterface $creation)
    {
        $this->creation = $creation;
        return $this;
    }

    /**
     * Get creation
     * @return \DateTime
     */
    public function getCreation(): ?DateTimeInterface
    {
        return $this->creation;
    }

    /**
     * Set enabled
     * @param bool $enabled If enabled
     * @return UserLight
     */
    public function setEnabled(bool $enabled)
    {
        $this->enabled = $enabled;
        return $this;
    }

    /**
     * Get enabled
     * @return bool
     */
    public function getEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * Set salt
     * @param string $salt Salt
     * @return UserLight
     */
    public function setSalt(?string $salt)
    {
        $this->salt = $salt;
        return $this;
    }

    /**
     * Get salt
     * @return string
     */
    public function getSalt(): ?string
    {
        return $this->salt;
    }

    /**
     * Set password
     * @param string $password Password
     * @return UserLight
     */
    public function setPassword(?string $password)
    {
        $this->password = $password;
        return $this;
    }

    /**
     * Get password
     * @return string
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    /**
     * Set token
     * @param string $token Token
     * @return UserLight
     */
    public function setToken(?string $token)
    {
        $this->token = $token;
        return $this;
    }

    /**
     * Get token
     * @return string
     */
    public function getToken(): ?string
    {
        return $this->token;
    }

    /**
     * Set passwordRequest
     * @param DateTime $passwordRequest Datetime for password request
     * @return UserLight
     */
    public function setPasswordRequest(?DateTimeInterface $passwordRequest)
    {
        $this->passwordRequest = $passwordRequest;
        return $this;
    }

    /**
     * Get passwordRequest
     * @return \DateTime
     */
    public function getPasswordRequest(): ?DateTimeInterface
    {
        return $this->passwordRequest;
    }

    /**
     * Set plainPassword
     * @param string $plainPassword Plain password (not stored)
     * @return UserLight
     */
    public function setPlainPassword(?string $plainPassword)
    {
        $this->plainPassword = $plainPassword;
        return $this;
    }

    /**
     * Get plainPassword
     * @return string
     */
    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    /**
     * Set challenge
     * @param string $challenge Challenge
     * @return UserLight
     */
    public function setChallenge(?string $challenge)
    {
        $this->challenge = $challenge;
        return $this;
    }

    /**
     * Get challenge
     * @return string
     */
    public function getChallenge(): ?string
    {
        return $this->challenge;
    }
}
