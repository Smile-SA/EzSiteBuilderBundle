<?php

namespace EdgarEz\SiteBuilderBundle\Service\Task;


interface TaskInterface
{
    public function validateParameters($parameters);

    public function execute($command, array $parameters);

    public function getMessage();
}
