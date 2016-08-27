<?php

namespace EdgarEz\SiteBuilderBundle\Form\Type;

use EdgarEz\SiteBuilderBundle\Form\Validator\Constraint\LocationIDConstraint;
use EdgarEz\SiteBuilderBundle\Form\Validator\Constraint\VendorNameConstraint;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
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
            ->add('vendorName', TextType::class, array(
                'required' => true,
                'constraints' => array(new VendorNameConstraint())
            ))
            ->add('contentLocationID', HiddenType::class, array(
                'constraints' => array(new LocationIDConstraint())
            ))
            ->add('mediaLocationID', HiddenType::class, array(
                'constraints' => array(new LocationIDConstraint())
            ))
            ->add('userLocationID', HiddenType::class, array(
                'constraints' => array(new LocationIDConstraint())
            ))
            ->add('install', SubmitType::class, ['label' => 'install.button']);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['translation_domain' => 'sitebuilder_install']);
    }
}
