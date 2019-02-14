<?php
/*
 * (c) 2018: 975L <contact@975l.com>
 * (c) 2018: Laurent Marquet <laurent.marquet@laposte.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace c975L\UserBundle\Entity\Traits;

use c975L\UserBundle\Entity\UserLight;
use Doctrine\ORM\Mapping as ORM;

/**
 * Trait UserSocialTrait
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 * @copyright 2018 975L <contact@975l.com>
 */
trait UserSocialTrait
{
    /**
     * Social network for the user
     * @var string
     *
     * @ORM\Column(name="social_network", type="string", nullable=true)
     */
    private $socialNetwork;

    /**
     * Social id for the user
     * @var string
     *
     * @ORM\Column(name="social_id", type="string", length=255, nullable=true)
     */
    private $socialId;

    /**
     * Social token for the user
     * @var string
     *
     * @ORM\Column(name="social_token", type="string", length=255, nullable=true)
     */
    private $socialToken;

    /**
     * Social picture url for the user
     * @var string
     *
     * @ORM\Column(name="social_picture", type="string", length=255, nullable=true)
     */
    private $socialPicture;


//GETTERS/SETTERS
    /**
     * Set socialNetwork
     * @param string $socialNetwork Social Network
     * @return UserLight
     */
    public function setSocialNetwork(?string $socialNetwork)
    {
        $this->socialNetwork = $socialNetwork;
        return $this;
    }

    /**
     * Get socialNetwork
     * @return string
     */
    public function getSocialNetwork(): ?string
    {
        return $this->socialNetwork;
    }

    /**
     * Set socialId
     * @param string $socialId Social id
     * @return UserLight
     */
    public function setSocialId(?string $socialId)
    {
        $this->socialId = $socialId;
        return $this;
    }

    /**
     * Get socialId
     * @return string
     */
    public function getSocialId(): ?string
    {
        return $this->socialId;
    }

    /**
     * Set socialToken
     * @param string $socialToken Social token
     * @return UserLight
     */
    public function setSocialToken(?string $socialToken)
    {
        $this->socialToken = $socialToken;
        return $this;
    }

    /**
     * Get socialToken
     * @return string
     */
    public function getSocialToken(): ?string
    {
        return $this->socialToken;
    }

    /**
     * Set socialPicture
     * @param string $socialPicture Social picture
     * @return UserLight
     */
    public function setSocialPicture(?string $socialPicture)
    {
        $this->socialPicture = $socialPicture;
        return $this;
    }

    /**
     * Get socialPicture
     * @return string
     */
    public function getSocialPicture(): ?string
    {
        return $this->socialPicture;
    }
}