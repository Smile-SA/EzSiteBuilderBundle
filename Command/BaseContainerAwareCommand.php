<?php

namespace EdgarEz\SiteBuilderBundle\Command;

use Sensio\Bundle\GeneratorBundle\Command\GeneratorCommand;
use Sensio\Bundle\GeneratorBundle\Command\Helper\QuestionHelper;
use Sensio\Bundle\GeneratorBundle\Manipulator\KernelManipulator;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\HttpKernel\KernelInterface;

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

    /**
     * Update AppKernel.php adding new sitebuilder project bundle
     *
     * @param QuestionHelper $questionHelper question Helper
     * @param InputInterface $input input console
     * @param OutputInterface $output output console
     * @param KernelInterface $kernel symfony Kernel
     * @param string $namespace project namespace
     * @param string $bundle project bundle name
     * @return array message to display at console output
     */
    protected function updateKernel(QuestionHelper $questionHelper, InputInterface $input, OutputInterface $output, KernelInterface $kernel, $namespace, $bundle)
    {
        $auto = true;
        if ($input->isInteractive()) {
            $question = new ConfirmationQuestion($questionHelper->getQuestion('Confirm automatic update of your Kernel', 'yes', '?'), true);
            $auto = $questionHelper->ask($input, $output, $question);
        }

        $output->write('Enabling the bundle inside the Kernel: ');
        $manip = new KernelManipulator($kernel);
        try {
            $ret = $auto ? $manip->addBundle($namespace . '\\' . $bundle) : false;

            if (!$ret) {
                $reflected = new \ReflectionObject($kernel);

                return array(
                    sprintf('- Edit <comment>%s</comment>', $reflected->getFilename()),
                    '  and add the following bundle in the <comment>AppKernel::registerBundles()</comment> method:',
                    '',
                    sprintf('    <comment>new %s(),</comment>', $namespace . '\\' . $bundle),
                    '',
                );
            }
        } catch (\RuntimeException $e) {
            return array(
                sprintf('Bundle <comment>%s</comment> is already defined in <comment>AppKernel::registerBundles()</comment>.', $namespace . '\\' . $bundle),
                '',
            );
        }
    }
}
