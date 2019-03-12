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
use Twig\Environment;
use Twig\TwigFunction;

/**
 * Twig extension to display user's information in a div data (mainly for javascript access) using `user_divData()`
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 * @copyright 2018 975L <contact@975l.com>
 */
class UserDivData extends AbstractExtension
{
    public function getFunctions()
    {
        return array(
            new TwigFunction(
                'user_divData',
                array($this, 'divData'),
                array(
                    'needs_environment' => true,
                    'is_safe' => array('html'),
                )
            ),
        );
    }

    /**
     * Returns the xhtml code for the div data
     * @retuirn string
     */
    public function divData(Environment $environment)
    {
        $render = $environment->render('@c975LUser/fragments/divData.html.twig');

        return str_replace(array("\n", '    ', '   ', '  '), ' ', $render);
    }
}
