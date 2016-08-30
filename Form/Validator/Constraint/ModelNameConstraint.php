<?php

namespace EdgarEz\SiteBuilderBundle\Form\Validator\Constraint;

use Symfony\Component\Validator\Constraint;

class ModelNameConstraint extends Constraint
{
    public $message = 'The string "%string%" contains an illegal character: it can only contain letters ' .
    'and should start with uppercase.';

    public function validatedBy()
    {
        return 'edgar_ez_site_builder.validator.modelname';
    }

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
