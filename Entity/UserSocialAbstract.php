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
use c975L\UserBundle\Entity\UserAbstract;

/**
 * Entity UserSocialAbstract
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 * @copyright 2018 975L <contact@975l.com>
 */
abstract class UserSocialAbstract extends UserAbstract
{
    /**
     * Social network for the user
     * @var string
     *
     * @ORM\Column(name="social_network", type="string", nullable=true)
     */
    protected $socialNetwork;

    /**
     * Social id for the user
     * @var string
     *
     * @ORM\Column(name="social_id", type="string", length=255, nullable=true)
     */
    protected $socialId;

    /**
     * Social token for the user
     * @var string
     *
     * @ORM\Column(name="social_token", type="string", length=255, nullable=true)
     */
    protected $socialToken;

    /**
     * Social picture url for the user
     * @var string
     *
     * @ORM\Column(name="social_picture", type="string", length=255, nullable=true)
     */
    protected $socialPicture;
}