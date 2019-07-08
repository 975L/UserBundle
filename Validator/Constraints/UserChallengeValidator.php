<?php
/*
 * (c) 2018: 975L <contact@975l.com>
 * (c) 2018: Laurent Marquet <laurent.marquet@laposte.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace c975L\UserBundle\Validator\Constraints;

use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Class to validate the answer to the Challenge number
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 * @copyright 2018 975L <contact@975l.com>
 */
class UserChallengeValidator extends ConstraintValidator
{
    /**
     * Validates the challenge
     */
    public function validate($value, Constraint $constraint)
    {
        $postChallengeResult = (string) strtoupper($value);

        $session = new Session();
        $sessionChallengeResult = (string) $session->get('challengeResult');

        if ($sessionChallengeResult !== null && $sessionChallengeResult !== $postChallengeResult) {
            $this->context
                ->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}
