<?php
/*
 * (c) 2018: 975L <contact@975l.com>
 * (c) 2018: Laurent Marquet <laurent.marquet@laposte.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace c975L\UserBundle\Twig;

class UserVat extends \Twig_Extension
{
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('user_vat', array($this, 'vat')),
        );
    }

    //Returns tva number formatted
    public function vat($number)
    {
        return sprintf("%s %s %s %s", substr($number, 0, 4), substr($number, 4, 3), substr($number, 7, 3), substr($number, 10, 3));
    }
}