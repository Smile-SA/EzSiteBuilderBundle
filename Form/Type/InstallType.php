<?php

namespace Smile\EzSiteBuilderBundle\Form\Type;

use Smile\EzSiteBuilderBundle\Form\Validator\Constraint\LocationIDConstraint;
use Smile\EzSiteBuilderBundle\Form\Validator\Constraint\VendorNameConstraint;
use Smile\EzSiteBuilderBundle\Service\InstallService;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class InstallType
 *
 * @package Smile\EzSiteBuilderBundle\Form\Type
 */
class InstallType extends AbstractType
{
    /** @var InstallService $installService */
    protected $installService;

    public function __construct(InstallService $installService)
    {
        $this->installService = $installService;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'smileezsb_forms_install';
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('vendorName', TextType::class, array(
                'label' => 'form.install.vendorname.label',
                'required' => true,
                'constraints' => array(new VendorNameConstraint())
            ))
            ->add('contentLocationID', HiddenType::class, array(
                'label' => 'form.install.contentlocationid.label',
                'constraints' => array(new LocationIDConstraint())
            ))
            ->add('mediaLocationID', HiddenType::class, array(
                'label' => 'form.install.medialocationid.label',
                'constraints' => array(new LocationIDConstraint())
            ))
            ->add('userLocationID', HiddenType::class, array(
                'label' => 'form.install.userlocationid.label',
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
