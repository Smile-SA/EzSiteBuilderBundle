<?php

namespace EdgarEz\SiteBuilderBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class InstallType
 *
 * @package EdgarEz\SiteBuilderBundle\Form\Type
 */
class InstallType extends AbstractType
{
    /**
     * @return string
     */
    public function getName()
    {
        return 'edgarezsb_forms_install';
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('vendorName', 'text')
            ->add('contentLocationID', HiddenType::class)
            ->add('mediaLocationID', HiddenType::class)
            ->add('userLocationID', HiddenType::class)
            ->add('install', 'submit', ['label' => 'install.button']);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['translation_domain' => 'sitebuilder_install']);
    }
}
