<?php

namespace Smile\EzSiteBuilderBundle\Form\Type;

use Smile\EzSiteBuilderBundle\Form\Validator\Constraint\CustomerNameConstraint;
use Smile\EzSiteBuilderBundle\Form\Validator\Constraint\UserEmailConstraint;
use Smile\EzSiteBuilderBundle\Form\Validator\Constraint\UserNameConstraint;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class CustomerType
 * @package Smile\EzSiteBuilderBundle\Form\Type
 */
class CustomerType extends AbstractType
{
    /**
     * @return string
     */
    public function getName()
    {
        return 'smileezsb_forms_customer';
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('customerName', TextType::class, array(
                'label' => 'form.customer.customername.label',
                'required' => true,
                'constraints' => array(new CustomerNameConstraint())
            ))
            ->add('userFirstName', TextType::class, array(
                'label' => 'form.customer.userfirstname.label',
                'required' => true,
                'constraints' => array(new UserNameConstraint())
            ))
            ->add('userLastName', TextType::class, array(
                'label' => 'form.customer.userlastname.label',
                'required' => true,
                'constraints' => array(new UserNameConstraint())
            ))
            ->add('userEmail', EmailType::class, array(
                'label' => 'form.customer.useremail.label',
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
