<?php

/*
 * Copyright (c) Michel Le Quer
 * All rights reserved.
 */

namespace MLequer\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;
use Symfony\Component\Console\Style\SymfonyStyle;
use MLequer\Entity\Project;

/**
 * The setup commmand
 *
 * @author michel
 */
class SetupCommand extends Command
{

    private $rootDir;
    private $templateFolder;

    /**
     * The composer dev dependencies
     * @var array
     */
    private $dependencies = [
        "squizlabs/php_codesniffer:2.*",
        "sebastian/phpdcd",
        "phpmd/phpmd:@stable",
        "phpunit/phpunit:5.*",
        "sebastian/phpcpd:*",
        "jakub-onderka/php-parallel-lint:*",
        "phpunit/php-code-coverage:*",
        "pdepend/pdepend:*",
        "phploc/phploc:*",
        "phing/phing:*",
            //  "sami/sami:dev-master@dev" currently waiting for console v3 support, use local
    ];

    /**
     *
     * @var SymfonyStyle
     */
    private $io;

    /**
     *
     * @var \Twig_Environment
     */
    private $twig;
    private $currentDirectory;
    private $project;

    protected function configure()
    {
        $this
                ->setName('setup')
                ->setDescription('Setup a project with phing for continous integration')
                ->addArgument('name', InputArgument::REQUIRED, 'Project name')
                ->addOption('destination', 'd', InputOption::VALUE_REQUIRED, 'Relative destination for build.xml', '.')
                ->addOption('build-folder', 'b', InputOption::VALUE_REQUIRED, 'Build folder', 'build')
                ->addOption('resources', 'r', InputOption::VALUE_REQUIRED, 'Destination folder for the resources', 'Resources')
                ->addOption('source', 's', InputOption::VALUE_REQUIRED, 'Source folder', 'src')
                ->addOption('tests', 't', InputOption::VALUE_REQUIRED, 'Tests folder', 'tests')
                ->addOption('configuration', 'c', InputOption::VALUE_OPTIONAL, 'configuration file')
                ->addOption('exclude', 'e', InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED, 'Exclude folders', ['vendor', 'build'])
                ->addOption('extensions', 'x', InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED, 'File extensions', ['php'])
                ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Dry run')
                ->addOption('skip-composer', null, InputOption::VALUE_NONE, 'Skip composer update')
                ->addOption('generate-config', 'g', InputOption::VALUE_NONE, 'Generate config from parameters')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->setupVariables($input->getOption('destination'), $input->getOption('resources'));
        $this->io = new SymfonyStyle($input, $output);

        $this->project = new Project($input->getArgument('name'), $input->getOptions());

        $buildXml = $this->twig->render('build.xml.twig', array('project' => $this->project));

        if ($input->getOption('dry-run')) {
            $output->writeln($buildXml);
            return;
        }
        $filesystem = new Filesystem();
        if ($input->getOption('generate-config')) {
            $config = $this->twig->render('.ci-setup.yml.twig', array('project' => $this->project));
            $filesystem->dumpFile('.ci-setup.yml', $config);
            $this->io->writeln($config);
            return;
        }


        if ($filesystem->exists('build.xml')) {
            if (!$this->io->confirm("Build file exists, overwrite?")) {
                return;
            }
        }
        // write the build file
        $filesystem->dumpFile('build.xml', $buildXml);
        // make the build and Resources directories
        $filesystem->mkdir([
            $this->project->build . '/logs',
            $this->project->build . '/pdepend',
            $this->project->resources
        ]);
        // render and write/copy the templates ,docs config and tests bootstrap
        $phpunit = $this->twig->render('phpunit.xml.twig', array('project' => $this->project));
        $filesystem->dumpFile('phpunit.xml', $phpunit);

        $sami = $this->twig->render('sami.php.twig', array('project' => $this->project));
        $filesystem->dumpFile($this->project->resources . '/sami.php', $sami);

        $sonar = $this->twig->render('sonar-project.properties.twig', array('project' => $this->project));
        $filesystem->dumpFile('sonar-project.properties', $sonar);

        $filesystem->copy($this->templateFolder . '/phpmd.xml', $this->project->resources . '/phpmd.xml');
        $filesystem->copy($this->templateFolder . '/TestBootstrap.php', $this->project->tests . '/TestBootstrap.php');

        // install composer dependencies
        if (!$input->getOption('skip-composer')) {
            $this->installComposerDependencies();
        }

        $this->io->success("PHP continuous integration setup done!");
    }

    /**
     * Set the root directory
     *
     * @param string $rootDir
     */
    public function setRootDir($rootDir)
    {
        $this->rootDir = $rootDir;
    }

    private function setupVariables()
    {
        if (!$this->rootDir) {
            throw new \RuntimeException("Root folder is missing");
        }
        $this->currentDirectory = getcwd();
        $this->templateFolder = $this->rootDir . '/templates';
        $this->initTwig();
    }

    private function initTwig()
    {
        \Twig_Autoloader::register();

        $loader = new \Twig_Loader_Filesystem($this->templateFolder);
        $this->twig = new \Twig_Environment($loader, array(
            'strict_variables' => true
        ));
    }

    private function installComposerDependencies()
    {
        $deps = implode(' ', $this->dependencies);
        $process = new Process("composer require --dev $deps");
        $process->run(function ($type, $buffer) {
            $this->io->writeln([
                "<info>Composer output [$type]:</info>",
                $buffer
            ]);
        });
    }
}
