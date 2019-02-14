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
use c975L\UserBundle\Entity\UserLight;

/**
 * Trait UserAddressTrait
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 * @copyright 2018 975L <contact@975l.com>
 */
trait UserDefaultTrait
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


//GETTERS/SETTERS
    /**
     * Set gender
     * @param string $gender Gender
     * @return UserLight
     */
    public function setGender(?string $gender)
    {
        $this->gender = $gender;
        return $this;
    }

    /**
     * Get gender
     * @return string
     */
    public function getGender(): ?string
    {
        return $this->gender;
    }

    /**
     * Set firstname
     * @param string $firstname Firstname
     * @return UserLight
     */
    public function setFirstname(?string $firstname)
    {
        $this->firstname = $firstname;
        return $this;
    }

    /**
     * Get firstname
     * @return string
     */
    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    /**
     * Set lastname
     * @param string $lastname Lastname
     * @return UserLight
     */
    public function setLastname(?string $lastname)
    {
        $this->lastname = $lastname;
        return $this;
    }

    /**
     * Get lastname
     * @return string
     */
    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    /**
     * Set avatar
     * @param string $avatar Avatar url
     * @return UserLight
     */
    public function setAvatar(?string $avatar)
    {
        $this->avatar = $avatar;
        return $this;
    }

    /**
     * Get avatar
     * @return string
     */
    public function getAvatar(): ?string
    {
        return $this->avatar;
    }

    /**
     * Set latestSignin
     * @param DateTime $latestSignin Datetime for latest signin
     * @return UserLight
     */
    public function setLatestSignin(?DateTimeInterface $latestSignin)
    {
        $this->latestSignin = $latestSignin;
        return $this;
    }

    /**
     * Get latestSignin
     * @return DateTime
     */
    public function getLatestSignin(): ?DateTimeInterface
    {
        return $this->latestSignin;
    }

    /**
     * Set latestSignout
     * @param DateTime $latestSignout Datetime for latest signout
     * @return UserLight
     */
    public function setLatestSignout(?DateTimeInterface $latestSignout)
    {
        $this->latestSignout = $latestSignout;
        return $this;
    }

    /**
     * Get latestSignout
     * @return DateTime
     */
    public function getLatestSignout(): DateTimeInterface
    {
        return $this->latestSignout;
    }

    /**
     * Set locale
     * @param string $locale Locale
     * @return UserLight
     */
    public function setLocale(?string $locale)
    {
        $this->locale = $locale;
        return $this;
    }

    /**
     * Get locale
     * @return string
     */
    public function getLocale(): ?string
    {
        return $this->locale;
    }
}