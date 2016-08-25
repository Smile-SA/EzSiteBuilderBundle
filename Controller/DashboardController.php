<?php

namespace EdgarEz\SiteBuilderBundle\Controller;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\EntityRepository;
use EdgarEz\SiteBuilderBundle\Entity\SiteBuilderTask;
use EzSystems\PlatformUIBundle\Controller\Controller;

class DashboardController extends Controller
{
    /** @var Registry $doctrineRegistry */
    protected $doctrineRegistry;

    public function __construct(
        Registry $doctrineRegistry
    )
    {
        $this->doctrineRegistry = $doctrineRegistry;
    }

    public function listAction($userID)
    {
        $datas = $this->getTasks($userID);

        return $this->render('EdgarEzSiteBuilderBundle:sb:tab/dashboard/list.html.twig', [
            'datas' => $datas
        ]);
    }

    protected function getTasks($userID)
    {
        $doctrineManager = $this->doctrineRegistry->getManager();
        /** @var EntityRepository $repository */
        $repository = $this->doctrineRegistry->getRepository('EdgarEzSiteBuilderBundle:SiteBuilderTask');

        $query = $repository->createQueryBuilder('t')
            ->where('t.userID = :userID')
            ->setParameter('userID', $userID)
            ->orderBy('t.postedAt', 'DESC')
            ->getQuery();

       return $query->getArrayResult();
    }
}
