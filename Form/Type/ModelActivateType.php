<?php

namespace EdgarEz\SiteBuilderBundle\Form\Type;

use EdgarEz\SiteBuilderBundle\Form\Validator\Constraint\LocationIDConstraint;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ModelActivateType extends AbstractType
{
    private $modelID;

    public function __construct($modelID = null)
    {
        $this->modelID = $modelID;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'edgarezsb_form_modelactivate';
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('modelID', HiddenType::class, array(
                'label' => 'form.model.modelid.label',
                'constraints' => array(new LocationIDConstraint()),
                'data' => $this->modelID
            ))
            ->add('modelactivate', SubmitType::class, ['label' => 'modelactivate.button']);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => '\EdgarEz\SiteBuilderBundle\Values\Content\ModelActivateStruct',
            'translation_domain' => 'edgarezsb_form_model',
        ]);
    }
}
