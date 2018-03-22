<?php
/*
 * (c) 2018: 975l <contact@975l.com>
 * (c) 2018: Laurent Marquet <laurent.marquet@laposte.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace c975L\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use c975L\UserBundle\Entity\UserAbstract;

/**
 * UserSocialAbstract
 */
abstract class UserSocialAbstract extends UserAbstract
{
    /**
     * @ORM\Column(name="social_network", type="string", nullable=true)
     */
    protected $socialNetwork;

    /**
     * @ORM\Column(name="social_id", type="string", length=255, nullable=true)
     */
    protected $socialId;

    /**
     * @ORM\Column(name="social_token", type="string", length=255, nullable=true)
     */
    protected $socialToken;

    /**
     * @ORM\Column(name="social_picture", type="string", length=255, nullable=true)
     */
    protected $socialPicture;
}