<?php
/**
 * Created by PhpStorm.
 * User: emdro
 * Date: 05/08/2016
 * Time: 15:24
 */

namespace EdgarEz\SiteBuilderBundle\Command;

use Sensio\Bundle\GeneratorBundle\Command\GeneratorCommand;
use Sensio\Bundle\GeneratorBundle\Command\Helper\QuestionHelper;

abstract class BaseContainerAwareCommand extends GeneratorCommand
{
    protected function getQuestionHelper()
    {
        $question = $this->getHelperSet()->get('question');
        if (!$question || get_class($question) !== 'Sensio\Bundle\GeneratorBundle\Command\Helper\QuestionHelper') {
            $this->getHelperSet()->set($question = new QuestionHelper());
        }

        return $question;
    }
}