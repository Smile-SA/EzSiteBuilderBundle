<?php

namespace EdgarEz\SiteBuilderBundle\Form\ActionDispatcher;

use EzSystems\RepositoryForms\Event\RepositoryFormEvents;
use EzSystems\RepositoryForms\Form\ActionDispatcher\AbstractActionDispatcher;
use Symfony\Component\OptionsResolver\OptionsResolver;

class InstallDispatcher extends AbstractActionDispatcher
{
    protected function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired('vendorName');
    }

    protected function getActionEventBaseName()
    {
        return 'sb_install';
    }
}
