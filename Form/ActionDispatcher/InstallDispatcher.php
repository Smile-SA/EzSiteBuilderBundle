<?php

namespace EdgarEz\SiteBuilderBundle\Form\ActionDispatcher;

use EzSystems\RepositoryForms\Form\ActionDispatcher\AbstractActionDispatcher;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class InstallDispatcher
 *
 * @package EdgarEz\SiteBuilderBundle\Form\ActionDispatcher
 */
class InstallDispatcher extends AbstractActionDispatcher
{
    /**
     * @param OptionsResolver $resolver
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired('vendorName');
    }

    /**
     * @return string
     */
    protected function getActionEventBaseName()
    {
        return 'sb_install';
    }
}
