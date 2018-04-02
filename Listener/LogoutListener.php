<?php
/*
 * (c) 2018: 975L <contact@975l.com>
 * (c) 2018: Laurent Marquet <laurent.marquet@laposte.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace c975L\UserBundle\Listener;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Logout\LogoutHandlerInterface;
use c975L\UserBundle\Entity\UserAbstract;

class LogoutListener implements LogoutHandlerInterface
{
    private $em;

    public function __construct(\Doctrine\ORM\EntityManager $em)
    {
        $this->em = $em;
    }

    public function logout(Request $request, Response $response, TokenInterface $token)
    {
        //Gets the user
        $user = $token->getUser();

        if (is_subclass_of($user, 'c975L\UserBundle\Entity\UserAbstract')) {
            //Writes signout time
            $user->setLatestSignout(new \DateTime());
            $this->em->persist($user);
            $this->em->flush();
        }
    }
}