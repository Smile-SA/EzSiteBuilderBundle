<?php

namespace EdgarEz\SiteBuilderBundle\Controller;

use Doctrine\Bundle\DoctrineBundle\Registry;
use EdgarEz\SiteBuilderBundle\Command\TaskCommand;
use EdgarEz\SiteBuilderBundle\Data\Install\InstallData;
use EdgarEz\SiteBuilderBundle\Data\Mapper\InstallMapper;
use EdgarEz\SiteBuilderBundle\Entity\SiteBuilderTask;
use EdgarEz\SiteBuilderBundle\Form\Type\InstallType;
use EdgarEz\SiteBuilderBundle\Values\Content\Install;
use eZ\Publish\Core\MVC\Symfony\Security\User;
use EzSystems\PlatformUIBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Acl\Exception\Exception;

class SbController extends Controller
{
    protected $tabItems;

    public function __construct($tabItems)
    {
        $this->tabItems = $tabItems;
    }

    public function sbAction()
    {
        $installed = $this->container->hasParameter('edgar_ez_site_builder.installed') ? $this->container->getParameter('edgar_ez_site_builder.installed') : false;
        $tabItems = $this->tabItems;

        if (!$installed) {
            $tabItems = array($tabItems[0], $tabItems[1]);
        } else {
            unset($tabItems[0]);
        }

        return $this->render('EdgarEzSiteBuilderBundle:sb:index.html.twig', [
            'installed' => $installed,
            'tab_items' => $tabItems
        ]);
    }

    public function tabAction($tabItem, $viewType)
    {
        $params = array();
        switch ($tabItem) {
            case 'install':
                $params['installForm'] = $this->createForm(
                    new InstallType()
                )->createView();
                break;
            default:
                break;
        }

        return $this->render('EdgarEzSiteBuilderBundle:sb:tab/' . $tabItem . '.html.twig', [
            'tab_items' => $this->tabItems,
            'tab_item' => $tabItem,
            'params' => $params,
            'view_type' => $viewType
        ]);
    }

    public function postInstallAction(Request $request)
    {
        $install = new Install([
            'vendorName' => 'Foo'
        ]);
        $installData = (new InstallMapper())->mapToFormData($install);

        $form = $this->createForm(new InstallType(), $installData);
        $form->handleRequest($request);
        if ($form->isValid()) {
            /** @var InstallData $data */
            $data = $form->getData();

            $action = array(
                'service'    => 'project',
                'command'    => 'install',
                'parameters' => array(
                    'vendorName'        => $data->vendorName,
                    'contentLocationID' => 2,
                    'mediaLocationID'   => 43,
                    'userLocationID'    => 5,
                )
            );

            /** @var Registry $dcotrineRegistry */
            $doctrineRegistry = $this->get('doctrine');
            $doctrineManager = $doctrineRegistry->getManager();

            $task = new SiteBuilderTask();

            try {
                $task->setAction($action);
                $task->setStatus(TaskCommand::STATUS_SUBMITTED);
                $task->setPostedAt(new \DateTime());
            } catch (Exception $e) {
                $task->setLogs('Fail to initialize task');
                $task->setStatus(TaskCommand::STATUS_FAIL);
            } finally {
                /** @var User $user */
                $user = $this->getUser();
                $task->setUserID($user->getAPIUser()->getUserId());

                $doctrineManager->persist($task);
                $doctrineManager->flush();
            }

            return $this->redirectAfterFormPost('edgarezsb_dashboard');
        }

        return $this->render('EdgarEzSiteBuilderBundle:sb:tab/install.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
