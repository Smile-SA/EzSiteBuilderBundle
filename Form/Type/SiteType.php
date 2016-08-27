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
    protected $modelsLocationID;

    public function __construct(
        LocationService $locationService,
        SearchService $searchService,
        $modelsLocationID
    )
    {
        $this->locationService = $locationService;
        $this->searchService = $searchService;
        $this->modelsLocationID = $modelsLocationID;
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
            ->add('siteName', TextType::class, array(
                'required' => true,
                'constraints' => array(new SiteNameConstraint())
            ))
            ->add('model', ChoiceType::class, array(
                'required' => true,
                'choices' => $this->getModels()
            ))
            ->add('host', TextType::class, array(
                'required' => true,
                'constraints' => array(new HostConstraint())
            ))
            ->add('mapuri', CheckboxType::class, array(
                'required' => false
            ))
            ->add('suffix', TextType::class, array(
                'required' => false,
                'constraints' => array(new HostSuffixConstraint())
            ))
            ->add('site', SubmitType::class, ['label' => 'site.button']);
    }

    private function getModels()
    {
        $models = array();

        $query = new \eZ\Publish\API\Repository\Values\Content\Query();
        $locationCriterion = new Query\Criterion\ParentLocationId($this->modelsLocationID);
        $contentTypeIdentifier = new Query\Criterion\ContentTypeIdentifier('edgar_ez_sb_model');

        $query->filter = new Query\Criterion\LogicalAnd(
            array($locationCriterion, $contentTypeIdentifier)
        );

        /** @var SearchResult $result */
        $result = $this->searchService->findContent($query);
        if ($result->totalCount) {
            foreach ($result->searchHits as $searchHit) {
                $models[$searchHit->valueObject->contentInfo->mainLocationId] = $searchHit->valueObject->contentInfo->name;
            }
        }

        return $models;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['translation_domain' => 'sitebuilder_site']);
    }
}
