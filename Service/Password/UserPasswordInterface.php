<?php
/*
 * (c) 2018: 975L <contact@975l.com>
 * (c) 2018: Laurent Marquet <laurent.marquet@laposte.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace c975L\UserBundle\Service\Password;

use Symfony\Component\HttpFoundation\Request;

/**
 * Interface to be called for DI for User Password related services
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 * @copyright 2018 975L <contact@975l.com>
 */
interface UserPasswordInterface
{
    /**
     * Changes the password
     */
    public function change($user);

    /**
     * Checks if delay has expired
     * @return bool
     */
    public function delayExpired($user);

    /**
     * Confirms the reset of password
     */
    public function resetConfirm($user);

    /**
     * Request to reset the password
     */
    public function resetRequest(Request $request, $formData);
}