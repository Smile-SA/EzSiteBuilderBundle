<?php

namespace Smile\EzSiteBuilderBundle\Form\ActionDispatcher;

use EzSystems\RepositoryForms\Form\ActionDispatcher\AbstractActionDispatcher;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class CustomerDispatcher
 * @package Smile\EzSiteBuilderBundle\Form\ActionDispatcher
 */
class CustomerDispatcher extends AbstractActionDispatcher
{
    /**
     * @param OptionsResolver $resolver
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired('customerName');
        $resolver->setRequired('userFirstName');
        $resolver->setRequired('userLastName');
        $resolver->setRequired('userEmail');
    }

    /**
     * @return string
     */
    protected function getActionEventBaseName()
    {
        return 'sb_customer';
    }
}
