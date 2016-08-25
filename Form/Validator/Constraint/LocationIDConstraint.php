<?php

namespace EdgarEz\SiteBuilderBundle\Form\Validator\Constraint;

use Symfony\Component\Validator\Constraint;

class LocationIDConstraint extends Constraint
{
    public $message = 'The entry "%string%" should be a valid Location ID.';
}
