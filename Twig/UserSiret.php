<?php
/*
 * (c) 2018: 975L <contact@975l.com>
 * (c) 2018: Laurent Marquet <laurent.marquet@laposte.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace c975L\UserBundle\Twig;

class UserSiret extends \Twig_Extension
{
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('user_siret', array($this, 'siret')),
        );
    }

    //Returns siret formatted
    public function siret($number)
    {
        return sprintf("%s %s %s %s", substr($number, 0, 3), substr($number, 3, 3), substr($number, 6, 3), substr($number, 9, 5));
    }
}