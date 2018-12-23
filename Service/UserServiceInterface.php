<?php
/*
 * (c) 2018: 975L <contact@975l.com>
 * (c) 2018: Laurent Marquet <laurent.marquet@laposte.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace c975L\UserBundle\Service;

use c975L\UserBundle\Entity\UserLight;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * Interface to be called for DI for User Main related services
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 * @copyright 2018 975L <contact@975l.com>
 */
interface UserServiceInterface
{
    /**
     * Adds the user
     */
    public function add($user);

    /**
     * Adds attempt for signin
     * @return array
     */
    public function addAttempt($error);

    /**
     * Adds role to user
     */
    public function addRole($user, string $role);

    /**
     * Archives the user using Stored Procedure
     */
    public function archive($userId);

    /**
     * Checks if profile is well filled
     */
    public function checkProfile($user);

    /**
     * Deletes the user
     */
    public function delete($user);

    /**
     * Deletes role to user
     */
    public function deleteRole($user, string $role);

    /**
     * Exports user's data
     * @return Response
     */
    public function export($user, $format);

    /**
     * Finds user by email
     * @return UserLight
     */
    public function findUserByEmail($email);

    /**
     * Finds user by id
     * @return UserLight
     */
    public function findUserById($id);

    /**
     * Finds user by identifier
     * @return UserLight
     */
    public function findUserByIdentifier($identifier);

    /**
     * Finds user by socialId
     * @return UserLight
     */
    public function findUserBySocialId($socialId);

    /**
     * Finds user by token
     * @return UserLight
     */
    public function findUserByToken($token);

    /**
     * Gets all the users
     * @return array
     */
    public function getUsersAll();

    /**
     * Gets the User entity used
     * @return string
     */
    public function getUserEntity();

    /**
     * Modifies the user
     */
    public function modify($user);

    /**
     * Registers the user
     */
    public function signup($user);

    /**
     * Confirms user's signup
     */
    public function signupConfirm($user);
}
