<?php

namespace EdgarEz\SiteBuilderBundle\Service\Task;

use Sensio\Bundle\GeneratorBundle\Manipulator\KernelManipulator;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Class BaseTaskService
 *
 * @package EdgarEz\SiteBuilderBundle\Service\Task
 */
abstract class BaseTaskService
{
    /** @var string $message task logs  */
    protected $message;

    /**
     * Update Symfony Kernel with new Bundle generated
     *
     * @param KernelInterface $kernel symfony kernel interface
     * @param string $namespace bundle namespace
     * @param string $bundle bundle name
     * @return array
     */
    protected function updateKernel(
        KernelInterface $kernel,
        $namespace,
        $bundle
    )
    {
        $auto = true;

        $manip = new KernelManipulator($kernel);
        try {
            $ret = $auto ? $manip->addBundle($namespace . '\\' . $bundle) : false;

            if (!$ret) {
                new \ReflectionObject($kernel);
            }
        } catch (\RuntimeException $e) {
            return array(
                sprintf('Bundle <comment>%s</comment> is already defined in <comment>AppKernel::registerBundles()</comment>.', $namespace . '\\' . $bundle),
                '',
            );
        }
    }

    /**
     * Return task logs
     *
     * @return string task logs
     */
    public function getMessage()
    {
        return $this->message;
    }
}

