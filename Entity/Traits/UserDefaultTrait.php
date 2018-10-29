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
}