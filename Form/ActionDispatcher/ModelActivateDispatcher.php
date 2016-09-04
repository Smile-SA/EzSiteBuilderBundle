<?php

namespace EdgarEz\SiteBuilderBundle\Form\ActionDispatcher;

use EzSystems\RepositoryForms\Form\ActionDispatcher\AbstractActionDispatcher;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ModelActivateDispatcher extends AbstractActionDispatcher
{
    /**
     * @param OptionsResolver $resolver
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired('modelID');
    }

    /**
     * @return string
     */
    protected function getActionEventBaseName()
    {
        return 'sb_modelactivate';
    }
}
