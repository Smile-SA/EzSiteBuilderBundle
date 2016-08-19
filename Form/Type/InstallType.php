<?php

namespace EdgarEz\SiteBuilderBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class InstallType extends AbstractType
{
    public function getName()
    {
        return 'edgarezsb_forms_install';
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('vendorName', 'text')
            ->add('install', 'submit', ['label' => 'install.button']);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['translation_domain' => 'sitebuilder_install']);
    }
}
