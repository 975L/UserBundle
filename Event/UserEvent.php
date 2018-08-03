<?php
/*
 * (c) 2018: 975L <contact@975l.com>
 * (c) 2018: Laurent Marquet <laurent.marquet@laposte.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace c975L\UserBundle\Event;

use Symfony\Component\EventDispatcher\Event;

class UserEvent extends Event
{
    const USER_DELETE = 'c975l_user.delete';
    const USER_MODIFY = 'c975l_user.modify';
    const USER_SIGNEDUP = 'c975l_user.signedup';
    const USER_SIGNIN = 'c975l_user.signin';
    const USER_SIGNUP = 'c975l_user.signup';
    const USER_SIGNUP_CONFIRM = 'c975l_user.signup.confirm';

    protected $user;
    protected $request;

    public function __construct($user, $request)
    {
        $this->user = $user;
        $this->request = $request;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function getRequest()
    {
        return $this->request;
    }
}