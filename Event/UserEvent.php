<?php
/*
 * (c) 2018: 975L <contact@975l.com>
 * (c) 2018: Laurent Marquet <laurent.marquet@laposte.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace c975L\UserBundle\Event;

use c975L\UserBundle\Entity\UserLight;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Events to be dispatched throughout the lifecycle of User Forms
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 * @copyright 2018 975L <contact@975l.com>
 */
class UserEvent extends Event
{
    /**
     * Used to dispatch event API "authenticate"
     */
    const API_USER_AUTHENTICATE = 'c975l_user.api.authenticate';

    /**
     * Used to dispatch event API "created"
     */
    const API_USER_CREATED = 'c975l_user.api.created';

    /**
     * Used to dispatch event API "delete"
     */
    const API_USER_DELETE = 'c975l_user.api.delete';

    /**
     * Used to dispatch event API "export"
     */
    const API_USER_EXPORT = 'c975l_user.api.export';

    /**
     * Used to dispatch event API "modify"
     */
    const API_USER_MODIFY = 'c975l_user.api.modify';

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
     */
    protected $user;

    /**
     * Stores Request
     * @var Request
     */
    protected $request;

    /**
     * Stores Response
     * @var Response
     */
    protected $response;

    public function __construct($user, Request $request, Response $response = null)
    {
        $this->user = $user;
        $this->request = $request;
        $this->response = $response;
    }

    /**
     * Gets User
     * @return UserLight
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Gets Request
     * @return Request
     */
    public function getRequest(): ?Request
    {
        return $this->request;
    }

    /**
     * Gets Response
     * @return Response
     */
    public function getResponse(): ?Response
    {
        return $this->response;
    }

    /**
     * Sets Response
     * @return Request
     */
    public function setResponse(?Response $response)
    {
        $this->response = $response;

        return $this;
    }
}