<?php

namespace EdgarEz\SiteBuilderBundle\Form\Validator\Constraint;

use Symfony\Component\Validator\Constraint;

class UserNameConstraint extends Constraint
{
    public $message = 'The string "%string%" contains an illegal character: it can only contain letters - or \'.';
}
