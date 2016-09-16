<?php

namespace Smile\EzSiteBuilderBundle\Service\Task;

use Symfony\Component\DependencyInjection\Container;

interface TaskInterface
{
    public function validateParameters($parameters);

    public function execute($command, array $parameters, Container $container, $userID);

    public function getMessage();
}
