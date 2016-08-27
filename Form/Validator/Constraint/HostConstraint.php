<?php

namespace EdgarEz\SiteBuilderBundle\Form\Validator\Constraint;

use Symfony\Component\Validator\Constraint;

class HostConstraint extends Constraint
{
    public $message = 'The string "%string%" is not a valid host.';
}
