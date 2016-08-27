<?php

namespace EdgarEz\SiteBuilderBundle\Form\Validator\Constraint;

use Symfony\Component\Validator\Constraint;

class CustomerNameConstraint extends Constraint
{
    public $message = 'The string "%string%" contains an illegal character: it can only contain letters and should start with uppercase.';
}
