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


/**
 * Interface to be called for DI for User Main related services
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 * @copyright 2018 975L <contact@975l.com>
 */
interface UserServiceInterface
{
    /**
     * Adds attempt for signin
     * @return array
     */
    public function addAttempt($error);

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
     * Exports user's data
     * @return Response
     */
    public function export($user, $format);

    /**
     * Finds user by email
     * @return User
     */
    public function findUserByEmail($email);

    /**
     * Finds user by id
     * @return User
     */
    public function findUserById($id);

    /**
     * Finds user by identifier
     * @return User
     */
    public function findUserByIdentifier($identifier);

    /**
     * Finds user by socialId
     * @return User
     */
    public function findUserBySocialId($socialId);

    /**
     * Finds user by token
     * @return User|null
     */
    public function findUserByToken($token);

    /**
     * Get the User entity used
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