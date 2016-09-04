<?php

namespace EdgarEz\SiteBuilderBundle\Form\Validator\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class ModelNameConstraintValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        if (!preg_match('/^[A-Z][a-zA-Z]*$/', $value)) {
            $this->context->addViolation(
                $constraint->message,
                array('%string%' => $value)
            );
        }
    }
}
