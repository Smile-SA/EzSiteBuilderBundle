<?php

namespace Smile\EzSiteBuilderBundle\Form\ActionDispatcher;

use EzSystems\RepositoryForms\Form\ActionDispatcher\AbstractActionDispatcher;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SitesDispatcher extends AbstractActionDispatcher
{
    /**
     * @param OptionsResolver $resolver
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired('model');
        $resolver->setRequired('siteName');
    }

    /**
     * @return string
     */
    protected function getActionEventBaseName()
    {
        return 'sb_sites';
    }
}
