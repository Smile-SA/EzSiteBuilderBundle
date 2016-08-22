<?php

namespace EdgarEz\SiteBuilderBundle\Service\Task;

use Sensio\Bundle\GeneratorBundle\Manipulator\KernelManipulator;
use Symfony\Component\HttpKernel\KernelInterface;

abstract class BaseTaskService
{
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
}