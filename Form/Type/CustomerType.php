<?php

namespace EdgarEz\SiteBuilderBundle\Form\Type;

use EdgarEz\SiteBuilderBundle\Form\Validator\Constraint\CustomerNameConstraint;
use EdgarEz\SiteBuilderBundle\Form\Validator\Constraint\UserEmailConstraint;
use EdgarEz\SiteBuilderBundle\Form\Validator\Constraint\UserNameConstraint;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class CustomerType
 * @package EdgarEz\SiteBuilderBundle\Form\Type
 */
class CustomerType extends AbstractType
{
    /**
     * @return string
     */
    public function getName()
    {
        return 'edgarezsb_forms_customer';
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('customerName', TextType::class, array(
                'required' => true,
                'constraints' => array(new CustomerNameConstraint())
            ))
            ->add('userFirstName', TextType::class, array(
                'required' => true,
                'constraints' => array(new UserNameConstraint())
            ))
            ->add('userLastName', TextType::class, array(
                'required' => true,
                'constraints' => array(new UserNameConstraint())
            ))
            ->add('userEmail', TextType::class, array(
                'required' => true,
                'constraints' => array(new UserEmailConstraint())
            ))
            ->add('customer', SubmitType::class, ['label' => 'customer.button']);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['translation_domain' => 'sitebuilder_customer']);
    }
}
