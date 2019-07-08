<?php
/*
 * (c) 2018: 975L <contact@975l.com>
 * (c) 2018: Laurent Marquet <laurent.marquet@laposte.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace c975L\UserBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Sets the message when Siret validation fails
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 * @copyright 2018 975L <contact@975l.com>
 *
 * @Annotation
 */
class Siret extends Constraint
{
    public $message = 'The SIRET number "%string%" is not valid.';
}