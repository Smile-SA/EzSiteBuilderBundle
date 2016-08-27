<?php

namespace EdgarEz\SiteBuilderBundle\Form\Type;

use EdgarEz\SiteBuilderBundle\Form\Validator\Constraint\ModelNameConstraint;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ModelType extends AbstractType
{
    /**
     * @return string
     */
    public function getName()
    {
        return 'edgarezsb_form_model';
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('modelName', TextType::class, array(
                'label' => 'form.model.label',
                'required' => true,
                'constraints' => array(new ModelNameConstraint())
            ))
            ->add('model', SubmitType::class, ['label' => 'model.button']);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => '\EdgarEz\SiteBuilderBundle\Values\Content\ModelStruct',
            'translation_domain' => 'edgarezsb_form_model',
        ]);
    }
}
