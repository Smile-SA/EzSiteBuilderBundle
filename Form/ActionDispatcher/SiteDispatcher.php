<?php

namespace EdgarEz\SiteBuilderBundle\Form\ActionDispatcher;

use EzSystems\RepositoryForms\Form\ActionDispatcher\AbstractActionDispatcher;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SiteDispatcher extends AbstractActionDispatcher
{
    /**
     * @param OptionsResolver $resolver
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired('siteName');
        $resolver->setRequired('model');
        $resolver->setRequired('host');
        $resolver->setRequired('mapuri');
    }

    /**
     * @return string
     */
    protected function getActionEventBaseName()
    {
        return 'sb_site';
    }
}
