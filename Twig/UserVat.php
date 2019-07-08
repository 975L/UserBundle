<?php
/*
 * (c) 2018: 975L <contact@975l.com>
 * (c) 2018: Laurent Marquet <laurent.marquet@laposte.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace c975L\UserBundle\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

/**
 * Twig extension to display the formatted VAT number using `|user_vat`
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 * @copyright 2018 975L <contact@975l.com>
 */
class UserVat extends AbstractExtension
{
    public function getFilters()
    {
        return array(
            new TwigFilter('user_vat', array($this, 'vat')),
        );
    }

    /**
     * Returns vat number formatted
     * @return string
     */
    public function vat($number)
    {
        return sprintf("%s %s %s %s", substr($number, 0, 4), substr($number, 4, 3), substr($number, 7, 3), substr($number, 10, 3));
    }
}
