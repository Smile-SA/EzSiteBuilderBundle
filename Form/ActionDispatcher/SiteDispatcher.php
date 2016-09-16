<?php

namespace Smile\EzSiteBuilderBundle\Form\ActionDispatcher;

use EzSystems\RepositoryForms\Form\ActionDispatcher\AbstractActionDispatcher;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SiteDispatcher extends AbstractActionDispatcher
{
    /**
     * @param OptionsResolver $resolver
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired('languageCode');
    }

    /**
     * @return string
     */
    protected function getActionEventBaseName()
    {
        return 'sb_site';
    }
}
