<?php
/**
 * Created by PhpStorm.
 * User: emdro
 * Date: 05/08/2016
 * Time: 12:56
 */

namespace EdgarEz\SiteBuilderBundle\Command;


use EdgarEz\SiteBuilderBundle\Generator\ModelGenerator;
use Sensio\Bundle\GeneratorBundle\Command\Validators;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;

class ModelCommand extends BaseContainerAwareCommand
{
    /**
     * Configure Model generator command
     */
    protected function configure()
    {
        $this
            ->setName('edgarez:sitebuilder:model:generate')
            ->setDescription('Generate SiteBuilder Model (Content Structure and Bundle)');
    }

    /**
     * Execute Model generator command
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $namespace = $this->getBundleNamespace($input, $output);

        $namespace = Validators::validateBundleNamespace($namespace, true);
        $bundle = strtr($namespace, array('\\' => ''));
        $bundle = Validators::validateBundleName($bundle);
        $dir = Validators::validateTargetDir($this->getDir($input, $output, $bundle, $namespace), $bundle, $namespace);

        $output->writeln(array(
            '',
            'Bundle namespace is <info>' . $namespace . '</info>',
            'Bundle name is <info>' . $bundle . '</info>',
            'Bundle directory is <info>' . $dir . '</info>',
            '',
        ));

        $questionHelper = $this->getQuestionHelper();

        $question = new ConfirmationQuestion($questionHelper->getQuestion('Do you want to purchase model generation', 'yes', '?'), true);
        if (!$questionHelper->ask($input, $output, $question)) {
            return;
        }

        /** @var $generator ModelGenerator */
        $generator = $this->getGenerator();
        // $generator->generate($bundle, $namespace, $dir);

        $output->writeln('Generating the bundle code: <info>OK</info>');
    }

    protected function getBundleNamespace(InputInterface $input, OutputInterface $output)
    {
        $questionHelper = $this->getQuestionHelper();

        $vendorName = false;
        while (!$vendorName) {
            $question = new Question($questionHelper->getQuestion('Model Vendor name used to construct namespace', null));
            $question->setValidator(
                array(
                    'EdgarEz\SiteBuilderBundle\Command\Validators',
                    'validateVendorName'
                )
            );
            $vendorName = $questionHelper->ask($input, $output, $question);
        }

        $modelName = false;
        while (!$modelName) {
            $question = new Question($questionHelper->getQuestion('Model name', null));
            $question->setValidator(
                array(
                    'EdgarEz\SiteBuilderBundle\Command\Validators',
                    'validateModelName'
                )
            );
            $modelName = $questionHelper->ask($input, $output, $question);
        }

        $namespace = $vendorName . '\\Models\\' . $modelName . 'Bundle';
        return $namespace;
    }

    protected function getDir(InputInterface $input, OutputInterface $output, $bundle, $namespace)
    {
        $questionHelper = $this->getQuestionHelper();

        $dir = false;
        while (!$dir) {
            $dir = dirname($this->getContainer()->getParameter('kernel.root_dir')).'/src';

            $output->writeln(array(
                '',
                'The bundle can be generated anywhere. The suggested default directory uses',
                'the standard conventions.',
                '',
            ));

            $question = new Question($questionHelper->getQuestion('Target directory', $dir), $dir);
            $question->setValidator(function ($dir) use ($bundle, $namespace) {
                return Validators::validateTargetDir($dir, $bundle, $namespace);
            });
            $dir = $questionHelper->ask($input, $output, $question);
        }

        return $dir;
    }

    protected function createGenerator()
    {
        return new ModelGenerator($this->getContainer()->get('filesystem'));
    }
}