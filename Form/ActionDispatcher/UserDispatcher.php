<?php

namespace Smile\EzSiteBuilderBundle\Form\ActionDispatcher;

use EzSystems\RepositoryForms\Form\ActionDispatcher\AbstractActionDispatcher;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserDispatcher extends AbstractActionDispatcher
{
    /**
     * @param OptionsResolver $resolver
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired('userType');
        $resolver->setRequired('userFirstName');
        $resolver->setRequired('userLastName');
        $resolver->setRequired('userEmail');
    }

    /**
     * @return string
     */
    protected function getActionEventBaseName()
    {
        return 'sb_user';
    }
}
