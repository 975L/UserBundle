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
use Symfony\Component\Validator\ConstraintValidator;

class SiretValidator extends ConstraintValidator
{
    //Validates the siret - http://devblog.lexik.fr/symfony/1-4-x/validation-dun-numero-siret-sfvalidatorsiret-1159
    public function validate($value, Constraint $constraint)
    {
        $siret = str_replace(array(' ', '.', '-', ',', ', '), '', trim($value));

        if (!empty($siret) && !preg_match('/^([0-9]{14})$/i', $siret)) {
            $this->context
                ->buildViolation($constraint->message)
                ->setParameter('%string%', $value)
                ->addViolation();
        } elseif (!empty($siret)) {
            $sum = 0;
            for ($i = 0; $i < 14; $i++) {
                if ($i % 2 == 0) {
                    $tmp = $siret[$i] * 2;
                    $tmp = $tmp > 9 ? $tmp - 9 : $tmp;
                } else {
                    $tmp= $siret[$i];
                }
                $sum += $tmp;
            }

            if ($sum % 10 !== 0) {
                $this->context
                    ->buildViolation($constraint->message)
                    ->setParameter('%string%', $value)
                    ->addViolation();
            }
        }
    }
}