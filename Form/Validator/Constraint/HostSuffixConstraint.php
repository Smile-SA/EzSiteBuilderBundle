<?php

namespace Smile\EzSiteBuilderBundle\Form\Validator\Constraint;

use Symfony\Component\Validator\Constraint;

class HostSuffixConstraint extends Constraint
{
    public $message = 'The string "%string%" is not a valid host suffix.';

    public function validatedBy()
    {
        return 'smile_ez_site_builder.validator.hostsuffix';
    }

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
