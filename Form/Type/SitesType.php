<?php

namespace Smile\EzSiteBuilderBundle\Form\Type;

use Smile\EzSiteBuilderBundle\Form\Validator\Constraint\SiteNameConstraint;
use eZ\Publish\API\Repository\SearchService;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Search\SearchResult;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SitesType extends AbstractType
{
    /** @var SearchService $searchService */
    protected $searchService;

    protected $contentRootModelsLocationID;
    protected $mediaRootModelsLocationID;
    protected $contentRootCustomerLocationID;
    protected $mediaRootCustomerLocationID;
    protected $customerName;

    public function __construct(
        SearchService $searchService,
        $contentRootModelsLocationID,
        $mediaRootModelsLocationID,
        $contentRootCustomerLocationID,
        $mediaRootCustomerLocationID,
        $customerName
    ) {
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
        return 'smileezsb_forms_sites';
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
            ->add('model', ChoiceType::class, array(
                'label' => 'form.sites.model.label',
                'required' => true,
                'choices' => $models
            ))
            ->add('siteName', TextType::class, array(
                'label' => 'form.site.sitename.label',
                'required' => true,
                'constraints' => array(new SiteNameConstraint())
            ))
            ->add('listSites', CollectionType::class, array(
                'entry_type' => SiteType::class,
                'required' => false,
            ))
            ->add('customerName', HiddenType::class, array('data' => $this->customerName))
            ->add('customerContentLocationID', HiddenType::class, array('data' => $this->contentRootCustomerLocationID))
            ->add('customerMediaLocationID', HiddenType::class, array('data' => $this->mediaRootCustomerLocationID))
            ->add('submit', SubmitType::class, ['label' => 'sites.button']);
    }

    private function getContentModels()
    {
        $models = array();

        $query = new Query();
        $locationCriterion = new Query\Criterion\ParentLocationId($this->contentRootModelsLocationID);
        $contentTypeIdentifier = new Query\Criterion\ContentTypeIdentifier('smile_ez_sb_model');
        $activated = new Query\Criterion\Field('activated', Query\Criterion\Operator::EQ, true);

        $query->filter = new Query\Criterion\LogicalAnd(
            array($locationCriterion, $contentTypeIdentifier, $activated)
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

        $query = new Query();
        $locationCriterion = new Query\Criterion\ParentLocationId($this->mediaRootModelsLocationID);
        $contentTypeIdentifier = new Query\Criterion\ContentTypeIdentifier('smile_ez_sb_mediamodel');

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
        $resolver->setDefaults(['translation_domain' => 'sitebuilder_sites']);
    }
}
