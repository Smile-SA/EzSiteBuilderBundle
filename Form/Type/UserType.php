<?php

namespace Smile\EzSiteBuilderBundle\Form\Type;

use Smile\EzSiteBuilderBundle\Form\Validator\Constraint\UserEmailConstraint;
use Smile\EzSiteBuilderBundle\Form\Validator\Constraint\UserNameConstraint;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType
{
    /**
     * @return string
     */
    public function getName()
    {
        return 'smileezsb_forms_user';
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('userType', ChoiceType::class, array(
                'label' => 'form.user.usertype.label',
                'required' => true,
                'choices' => array(
                    'Editor', 'Creator'
                )
            ))
            ->add('userFirstName', TextType::class, array(
                'label' => 'form.user.userfirstname.label',
                'required' => true,
                'constraints' => array(new UserNameConstraint())
            ))
            ->add('userLastName', TextType::class, array(
                'label' => 'form.user.userlastname.label',
                'required' => true,
                'constraints' => array(new UserNameConstraint())
            ))
            ->add('userEmail', EmailType::class, array(
                'label' => 'form.user.useremail.label',
                'required' => true,
                'constraints' => array(new UserEmailConstraint())
            ))
            ->add('user', SubmitType::class, ['label' => 'user.button']);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['translation_domain' => 'sitebuilder_user']);
    }
}
