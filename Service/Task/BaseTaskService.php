<?php

namespace Smile\EzSiteBuilderBundle\Service\Task;

use Sensio\Bundle\GeneratorBundle\Manipulator\KernelManipulator;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Class BaseTaskService
 *
 * @package Smile\EzSiteBuilderBundle\Service\Task
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
    ) {
        $auto = true;

        $manip = new KernelManipulator($kernel);
        try {
            $ret = $auto ? $manip->addBundle($namespace . '\\' . $bundle) : false;

            if (!$ret) {
                new \ReflectionObject($kernel);
            }
        } catch (\RuntimeException $e) {
            return array(
                sprintf(
                    'Bundle <comment>%s</comment> is already defined in ' .
                    '<comment>AppKernel::registerBundles()</comment>.',
                    $namespace . '\\' . $bundle
                ),
                ''
            );
        }
    }

    protected function cacheClear(Kernel $kernel)
    {
        $application = new Application($this->kernel);
        $application->setAutoExit(false);

        $input = new ArrayInput(array(
            'command' => 'cache:clear',
            '--env' => $kernel->getEnvironment(),
        ));
        $output = new BufferedOutput();
        $application->run($input, $output);
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
