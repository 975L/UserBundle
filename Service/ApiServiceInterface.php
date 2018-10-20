<?php
/*
 * (c) 2018: 975L <contact@975l.com>
 * (c) 2018: Laurent Marquet <laurent.marquet@laposte.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace c975L\UserBundle\Service;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * Interface to be called for DI for API Main related services
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 * @copyright 2018 975L <contact@975l.com>
 */
interface ApiServiceInterface
{
    /**
     * Creates the user using the API
     */
    public function create($user, ParameterBag $parameters);

    /**
     * Deletes the user
     */
    public function delete($user);

    /**
     * Hydrates the user with given parameters
     */
    public function hydrate($user, ParameterBag $parameters);

    /**
     * Modifies the user
     */
    public function modify($user);
}
