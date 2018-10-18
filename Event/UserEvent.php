<?php
/*
 * (c) 2018: 975L <contact@975l.com>
 * (c) 2018: Laurent Marquet <laurent.marquet@laposte.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace c975L\UserBundle\Event;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\EventDispatcher\Event;

/**
 * Events to be dispatched throughout the lifecycle of User Forms
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 * @copyright 2018 975L <contact@975l.com>
 */
class UserEvent extends Event
{
    /**
     * Used to dispatch event "delete"
     */
    const USER_DELETE = 'c975l_user.delete';

    /**
     * Used to dispatch event "modify"
     */
    const USER_MODIFY = 'c975l_user.modify';

    /**
     * Used to dispatch event "signedup"
     */
    const USER_SIGNEDUP = 'c975l_user.signedup';

    /**
     * Used to dispatch event "signin"
     */
    const USER_SIGNIN = 'c975l_user.signin';

    /**
     * Used to dispatch event "signup"
     */
    const USER_SIGNUP = 'c975l_user.signup';

    /**
     * Used to dispatch event "signup.confirm"
     */
    const USER_SIGNUP_CONFIRM = 'c975l_user.signup.confirm';

    /**
     * Stores User
     * @var User
     */
    protected $user;

    /**
     * Stores Request
     * @var Request
     */
    protected $request;

    public function __construct($user, Request $request)
    {
        $this->user = $user;
        $this->request = $request;
    }

    /**
     * Get User
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Get Request
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }
}