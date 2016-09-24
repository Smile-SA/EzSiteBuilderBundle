<?php

namespace Smile\EzSiteBuilderBundle\Service\Task;

use Symfony\Bundle\FrameworkBundle\Command\CacheClearCommand;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpKernel\Kernel;

/**
 * Class CacheTaskService
 * @package Smile\EzSiteBuilderBundle\Service\Task
 */
class CacheTaskService extends BaseTaskService implements TaskInterface
{
    /** @var CacheClearCommand $cacheClearCommand */
    protected $cacheClearCommand;

    /** @var Kernel $kernel */
    protected $kernel;

    public function __construct(
        CacheClearCommand $cacheClearCommand,
        Kernel $kernel
    ) {
        $this->cacheClearCommand = $cacheClearCommand;
        $this->kernel = $kernel;

        $this->message = false;
    }

    /**
     * Execute task
     *
     * @param string $command
     * @param array $parameters
     * @return bool
     */
    public function execute($command, array $parameters, Container $container, $userID)
    {
        switch ($command) {
            case 'clear':
                try {
                    $this->cacheClear($this->cacheClearCommand, $this->kernel);
                } catch (\RuntimeException $e) {
                    $this->message = $e->getMessage();
                    return false;
                } catch (\Exception $e) {
                    $this->message = $e->getMessage();
                    return false;
                }
                break;
            default:
                break;
        }

        return true;
    }

    protected function cacheClear(CacheClearCommand $cacheClearCommand, Kernel $kernel)
    {
        $input = new ArgvInput(array('--env=' . $kernel->getEnvironment()));
        $output = new BufferedOutput();
        $cacheClearCommand->run($input, $output);
    }
}
