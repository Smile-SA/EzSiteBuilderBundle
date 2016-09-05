<?php

namespace EdgarEz\SiteBuilderBundle\Form\Type;

use EdgarEz\SiteBuilderBundle\Form\Validator\Constraint\LocationIDConstraint;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SiteActivateType extends AbstractType
{
    private $siteID;

    public function __construct($siteID = null)
    {
        $this->siteID = $siteID;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'edgarezsb_form_siteactivate';
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('siteID', HiddenType::class, array(
                'label' => 'form.site.siteid.label',
                'constraints' => array(new LocationIDConstraint()),
                'data' => $this->siteID
            ))
            ->add('siteactivate', SubmitType::class, ['label' => 'siteactivate.button']);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => '\EdgarEz\SiteBuilderBundle\Values\Content\SiteActivateStruct',
            'translation_domain' => 'edgarezsb_form_site',
        ]);
    }
}
