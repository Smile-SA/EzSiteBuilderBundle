<?php

namespace Smile\EzSiteBuilderBundle\Form\ActionDispatcher;

use EzSystems\RepositoryForms\Form\ActionDispatcher\AbstractActionDispatcher;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class InstallDispatcher
 *
 * @package Smile\EzSiteBuilderBundle\Form\ActionDispatcher
 */
class InstallDispatcher extends AbstractActionDispatcher
{
    /**
     * @param OptionsResolver $resolver
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired('vendorName');
        $resolver->setRequired('contentLocationID');
        $resolver->setRequired('mediaLocationID');
        $resolver->setRequired('userLocationID');
    }

    /**
     * @return string
     */
    protected function getActionEventBaseName()
    {
        return 'sb_install';
    }
}
