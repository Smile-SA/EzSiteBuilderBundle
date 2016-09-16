<?php

namespace Smile\EzSiteBuilderBundle\Form\Validator\Constraint;

use Symfony\Component\Validator\Constraint;

class LocationIDConstraint extends Constraint
{
    public $message = 'The entry "%string%" should be a valid Location ID.';

    public function validatedBy()
    {
        return 'smile_ez_site_builder.validator.locationid';
    }

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
