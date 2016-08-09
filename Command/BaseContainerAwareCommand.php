<?php

namespace EdgarEz\SiteBuilderBundle\Command;

use Sensio\Bundle\GeneratorBundle\Command\GeneratorCommand;
use Sensio\Bundle\GeneratorBundle\Command\Helper\QuestionHelper;

/**
 * Class BaseContainerAwareCommand
 *
 * Abstract class embeded questionHelper and extended sensio GeneratorCommand
 *
 * @package EdgarEz\SiteBuilderBundle\Command
 */
abstract class BaseContainerAwareCommand extends GeneratorCommand
{
    /**
     * Get questionHelper
     *
     * @return QuestionHelper|\Symfony\Component\Console\Helper\HelperInterface
     */
    protected function getQuestionHelper()
    {
        $question = $this->getHelperSet()->get('question');
        if (!$question || get_class($question) !== 'Sensio\Bundle\GeneratorBundle\Command\Helper\QuestionHelper') {
            $this->getHelperSet()->set($question = new QuestionHelper());
        }

        return $question;
    }
}
