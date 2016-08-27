<?php

namespace EdgarEz\SiteBuilderBundle\Command;

use EdgarEz\SiteBuilderBundle\Generator\ProjectGenerator;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;
use Sensio\Bundle\GeneratorBundle\Command\GeneratorCommand;
use Sensio\Bundle\GeneratorBundle\Command\Helper\QuestionHelper;
use Sensio\Bundle\GeneratorBundle\Manipulator\KernelManipulator;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\DependencyInjection\Container;
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
     * @var string $vendorName namespace vendor name where project sitebuilder will be generated
     */
    protected $vendorName;

    /**
     * @var string $dir system directory where bundle would be generated
     */
    protected $dir;

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
    protected function updateKernel(
        QuestionHelper $questionHelper,
        InputInterface $input,
        OutputInterface $output,
        KernelInterface $kernel,
        $namespace,
        $bundle
    )
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

    /**
     * Ask for Project Bundle vendor name
     *
     * @param InputInterface $input input console
     * @param OutputInterface $output output console
     * @return bool|string
     */
    protected function getVendorName(InputInterface $input, OutputInterface $output)
    {
        $questionHelper = $this->getQuestionHelper();

        $vendorName = false;
        $question = new Question($questionHelper->getQuestion('Vendor name used to construct namespace', null));
        $question->setValidator(
            array(
                'EdgarEz\SiteBuilderBundle\Command\Validators',
                'validateVendorName'
            )
        );

        while (!$vendorName) {
            try {
                $vendorName = $questionHelper->ask($input, $output, $question);
            } catch (InvalidArgumentException $e) {
                $output->write('<error>' . $e->getMessage() . '</error>');
            }
        }

        return $vendorName;
    }

    /**
     * Ask for directory where to install project bundles
     *
     * @param InputInterface $input input console
     * @param OutputInterface $output output console
     * @return bool|string
     */
    protected function getDir(InputInterface $input, OutputInterface $output)
    {
        $questionHelper = $this->getQuestionHelper();

        $dir = false;
        $dirSuggest = dirname($this->getContainer()->getParameter('kernel.root_dir')).'/src';

        $output->writeln(array(
            '',
            'The bundle can be generated anywhere. The suggested default directory uses',
            'the standard conventions.',
            '',
        ));

        $question = new Question($questionHelper->getQuestion('Target directory', $dirSuggest), $dirSuggest);
        $question->setValidator(
            array(
                'EdgarEz\SiteBuilderBundle\Command\Validators',
                'validateTargetDir'
            )
        );

        while (!$dir) {
            try {
                $dir = $questionHelper->ask($input, $output, $question);
            } catch (InvalidArgumentException $e) {
                $output->write('<error>' . $e->getMessage() . '</error>');
            }
        }

        return $dir;
    }

    /**
     * Initialize command by asking vendor name and directory bundles
     *
     * @param InputInterface $input input console
     * @param OutputInterface $output output console
     */
    protected function init(InputInterface $input, OutputInterface $output)
    {
        $this->vendorName = $this->getVendorName($input, $output);
        $this->dir = $this->getDir($input, $output);
    }

    /**
     * Initialize vendor name and directory bundles from parameters
     */
    protected function getVendorNameDir()
    {
        $basename = substr(ProjectGenerator::BUNDLE, 0, -6);
        $extensionAlias = 'edgarez_sb.' . Container::underscore($basename);

        $this->vendorName = $this->getContainer()->getParameter($extensionAlias . '.default.vendor_name');
        $this->dir = $this->getContainer()->getParameter($extensionAlias . '.default.dir');
    }
}
