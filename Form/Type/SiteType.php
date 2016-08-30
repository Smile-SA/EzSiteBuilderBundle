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

    public function __construct(
        LocationService $locationService,
        SearchService $searchService,
        $contentRootModelsLocationID,
        $mediaRootModelsLocationID,
        $contentRootCustomerLocationID,
        $mediaRootCustomerLocationID,
        $customerName
    ) {
        $this->locationService = $locationService;
        $this->searchService = $searchService;
        $this->contentRootModelsLocationID = $contentRootModelsLocationID;
        $this->mediaRootModelsLocationID = $mediaRootModelsLocationID;
        $this->contentRootCustomerLocationID = $contentRootCustomerLocationID;
        $this->mediaRootCustomerLocationID = $mediaRootCustomerLocationID;
        $this->customerName = $customerName;
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
        $contentModels = $this->getContentModels();
        $mediaModels = $this->getMediaModels();
        $models = array();

        foreach ($contentModels as $idContent => $contentModel) {
            foreach ($mediaModels as $idMedia => $mediaModel) {
                if ($contentModel == $mediaModel) {
                    $models[$idContent . '-' . $idMedia] = $mediaModel;
                    break;
                }
            }
        }

        $builder
            ->add('siteName', TextType::class, array(
                'label' => 'form.site.sitename.label',
                'required' => true,
                'constraints' => array(new SiteNameConstraint())
            ))
            ->add('model', ChoiceType::class, array(
                'label' => 'form.site.model.label',
                'required' => true,
                'choices' => $models
            ))
            ->add('host', TextType::class, array(
                'label' => 'form.site.host.label',
                'required' => true,
                'constraints' => array(new HostConstraint())
            ))
            ->add('mapuri', CheckboxType::class, array(
                'label' => 'form.site.mapuri.label',
                'required' => false
            ))
            ->add('suffix', TextType::class, array(
                'label' => 'form.site.suffix.label',
                'required' => false,
                'constraints' => array(new HostSuffixConstraint())
            ))
            ->add('customerName', HiddenType::class, array('data' => $this->customerName))
            ->add('customerContentLocationID', HiddenType::class, array('data' => $this->contentRootCustomerLocationID))
            ->add('customerMediaLocationID', HiddenType::class, array('data' => $this->mediaRootCustomerLocationID))
            ->add('site', SubmitType::class, ['label' => 'site.button']);
    }

    private function getContentModels()
    {
        $models = array();

        $query = new \eZ\Publish\API\Repository\Values\Content\Query();
        $locationCriterion = new Query\Criterion\ParentLocationId($this->contentRootModelsLocationID);
        $contentTypeIdentifier = new Query\Criterion\ContentTypeIdentifier('edgar_ez_sb_model');

        $query->filter = new Query\Criterion\LogicalAnd(
            array($locationCriterion, $contentTypeIdentifier)
        );

        /** @var SearchResult $result */
        $result = $this->searchService->findContent($query);
        if ($result->totalCount) {
            foreach ($result->searchHits as $searchHit) {
                $key = $searchHit->valueObject->contentInfo->mainLocationId;
                $models[$key] = $searchHit->valueObject->contentInfo->name;
            }
        }

        return $models;
    }

    private function getMediaModels()
    {
        $models = array();

        $query = new \eZ\Publish\API\Repository\Values\Content\Query();
        $locationCriterion = new Query\Criterion\ParentLocationId($this->mediaRootModelsLocationID);
        $contentTypeIdentifier = new Query\Criterion\ContentTypeIdentifier('edgar_ez_sb_mediamodel');

        $query->filter = new Query\Criterion\LogicalAnd(
            array($locationCriterion, $contentTypeIdentifier)
        );

        /** @var SearchResult $result */
        $result = $this->searchService->findContent($query);
        if ($result->totalCount) {
            foreach ($result->searchHits as $searchHit) {
                $key = $searchHit->valueObject->contentInfo->mainLocationId;
                $models[$key] = $searchHit->valueObject->contentInfo->name;
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
