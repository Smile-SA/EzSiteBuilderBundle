<?php

namespace Smile\EzSiteBuilderBundle\Controller;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Smile\EzSiteBuilderBundle\Entity\SiteBuilderTask;
use EzSystems\PlatformUIBundle\Controller\Controller;

class DashboardController extends Controller
{
    /** @var Registry $doctrineRegistry */
    protected $doctrineRegistry;

    public function __construct(
        Registry $doctrineRegistry
    ) {
        $this->doctrineRegistry = $doctrineRegistry;
    }

    public function listAction($userID)
    {
        $datas = $this->getTasks($userID);

        return $this->render('SmileEzSiteBuilderBundle:sb:tab/dashboard/list.html.twig', [
            'datas' => $datas
        ]);
    }

    protected function getTasks($userID)
    {
        $repository = $this->doctrineRegistry->getRepository('SmileEzSiteBuilderBundle:SiteBuilderTask');

        $query = $repository->createQueryBuilder('t')
            ->where('t.userID = :userID')
            ->setParameter('userID', $userID)
            ->orderBy('t.postedAt', 'DESC')
            ->getQuery();


        /** @var SiteBuilderTask[] $result */
        $result = $query->getArrayResult();
        return $result;
    }
}
