<?php

namespace EdgarEz\SiteBuilderBundle\Form\Validator\Constraint;

use Symfony\Component\Validator\Constraint;

class UserNameConstraint extends Constraint
{
    public $message = 'The string "%string%" contains an illegal character: it can only contain letters - or \'.';

    public function validatedBy()
    {
        return 'edgar_ez_site_builder.validator.username';
    }

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
