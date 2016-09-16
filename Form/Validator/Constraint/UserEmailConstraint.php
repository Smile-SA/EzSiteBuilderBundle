<?php

namespace Smile\EzSiteBuilderBundle\Form\Validator\Constraint;

use Symfony\Component\Validator\Constraint;

class UserEmailConstraint extends Constraint
{
    public $message = 'The string "%string%" is not a valid email.';

    public function validatedBy()
    {
        return 'smile_ez_site_builder.validator.useremail';
    }

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
