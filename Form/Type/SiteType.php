<?php

namespace EdgarEz\SiteBuilderBundle\Form\Type;

use EdgarEz\SiteBuilderBundle\Form\Validator\Constraint\HostConstraint;
use EdgarEz\SiteBuilderBundle\Form\Validator\Constraint\HostSuffixConstraint;
use EdgarEz\SiteBuilderBundle\Form\Validator\Constraint\SiteNameConstraint;
use eZ\Publish\API\Repository\LocationService;
use eZ\Publish\API\Repository\SearchService;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Search\SearchResult;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SiteType extends AbstractType
{
    /** @var LocationService $locationService */
    protected $locationService;

    /** @var SearchService $searchService */
    protected $searchService;

    /** @var int $modelsLocationID */
    protected $contentRootModelsLocationID;

    /** @var int $mediaRootModelsLocationID */
    protected $mediaRootModelsLocationID;

    /** @var int $contentRootCustomerLocationID */
    protected $contentRootCustomerLocationID;

    /** @var int $mediaRootCustomerLocationID */
    protected $mediaRootCustomerLocationID;

    /** @var string $customerName */
    protected $customerName;

    /** @var string $languageCode */
    protected $languageCode;

    public function __construct($languageCode)
    {
        $this->languageCode = $languageCode;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'edgarezsb_forms_site';
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('languageCode', HiddenType::class, array(
                'label' => 'form.site.languagecode.label'
            ))
            ->add('host', TextType::class, array(
                'label' => 'form.site.host.label',
                'required' => false,
                'constraints' => array(new HostConstraint())
            ))
            ->add('suffix', TextType::class, array(
                'label' => 'form.site.suffix.label',
                'required' => false,
                'constraints' => array(new HostSuffixConstraint())
            ));
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['translation_domain' => 'sitebuilder_site']);
    }
}
