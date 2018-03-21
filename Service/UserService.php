<?php
/*
 * (c) 2018: 975l <contact@975l.com>
 * (c) 2018: Laurent Marquet <laurent.marquet@laposte.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace c975L\UserBundle\Service;

class UserService
{
    private $container;
    private $request;
    private $em;

    public function __construct(
        \Symfony\Component\DependencyInjection\ContainerInterface $container,
        \Symfony\Component\HttpFoundation\RequestStack $requestStack,
        \Doctrine\ORM\EntityManager $em
    )
    {
        $this->container = $container;
        $this->request = $requestStack->getCurrentRequest();
        $this->em = $em;
    }

    //Checks if profile is well filled
    public function checkProfile($user)
    {
        if (
            (!empty($this->container->getParameter('c975_l_user.multilingual')) && $user->getLocale() === null) ||
            ($this->container->getParameter('c975_l_user.address') === true && $user->getAddress() === null) ||
            ($this->container->getParameter('c975_l_user.business') === true && $user->getBusinessType() === null)
            ) {
            //Creates flash
            $translator = $this->container->get('translator');
            $flash = $translator->trans('text.update_profile_missing_info', array(), 'user');
            $this->request->getSession()
                ->getFlashBag()
                ->add('warning', $flash)
                ;

            return false;
        }

        return true;
    }

    //Finds user by id
    public function findUserById($id)
    {
        return $this->em->getRepository('c975LUserBundle:User')->findOneById($id);
    }

    //Gets url from a Route
    public function getUrlFromRoute($route)
    {
        //Gets Route
        $routeValue = trim(substr($route, 0, strpos($route, ',')), "\'\"");

        //Gets parameters
        $params = trim(substr($route, strpos($route, '{')), "{}");
        $params = str_replace(array('"', "'"), '', $params);
        $params = explode(',', $params);

        //Caculates url
        $paramsArray = array();
        foreach($params as $value) {
            $parameter = explode(':', $value);
            $paramsArray[trim($parameter[0])] = trim($parameter[1]);
        }

        return array(
            'route' => $routeValue,
            'params' => $paramsArray
        );
    }
}