<?php
/*
 * (c) 2018: 975l <contact@975l.com>
 * (c) 2018: Laurent Marquet <laurent.marquet@laposte.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace c975L\UserBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class TvaValidator extends ConstraintValidator
{
    //Validates the tva number - http://devblog.lexik.fr/symfony/un-validator-tva-bien-pratique-1123
    public function validate($value, Constraint $constraint)
    {
        //Sample number (pages jaunes) FR 12 444 212 955
        $tva = str_replace(array(' ', '.', '-', ',', ', '), '', trim(strtoupper($value)));

        if (!empty($tva) && !preg_match('/^([A-Z]{2}[0-9A-Z]{2,12})$/i', $tva)) {
            $this->context
                ->buildViolation($constraint->message)
                ->setParameter('%string%', $value)
                ->addViolation();
        } elseif (!empty($tva)) {
            //Defines data
            $countryCode = substr($tva, 0, 2);
            $tvaNumber = substr($tva, 2);

            //Calls webservice
            $client = new \SoapClient('http://ec.europa.eu/taxation_customs/vies/checkVatService.wsdl');
            $params = array('countryCode' => $countryCode, 'vatNumber' => $tvaNumber);
            $result = $client->checkVat($params);

            //Checks validity
            if (!$result->valid) {
                $this->context
                    ->buildViolation($constraint->message)
                    ->setParameter('%string%', $value)
                    ->addViolation();
            }
        }
    }
}