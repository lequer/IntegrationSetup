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
use Symfony\Component\Finder\Finder;

/**
 * The setup commmand
 *
 * @author michel
 */
class SetupCommand extends Command {

    private $rootDir;
    private $resourceFolder;
    private $twigCache;
    /**
     *
     * @var \Twig_Environment
     */
    private $twig;
    private $currentDirectory;
    
    private $project;

    protected function configure() {
        $this
                ->setName('setup')
                ->setDescription('Setup a project with phing for continous integration')
                ->addArgument('name', InputArgument::REQUIRED, 'Project name')
                ->addOption('destination','d', InputOption::VALUE_REQUIRED, 'Relative destination for build.xml', '.')
                ->addOption('build-folder','b', InputOption::VALUE_REQUIRED, 'Build folder', 'build')
                ->addOption('resources', 'r', InputOption::VALUE_REQUIRED, 'Destination folder for the resources', 'resources')
                ->addOption('source', 's', InputOption::VALUE_REQUIRED, 'Source folder', 'src')
                ->addOption('tests', 't', InputOption::VALUE_REQUIRED, 'Tests folder', 'tests')
                ->addOption('exclude', 'e', InputOption::VALUE_IS_ARRAY|InputOption::VALUE_REQUIRED, 'Exclude folders', ['vendor'])
                ->addOption('extension', 'x', InputOption::VALUE_IS_ARRAY|InputOption::VALUE_REQUIRED, 'File extension', ['php'])
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $this->setupVariables($input->getOption('destination'), $input->getOption('resources'));
        
        $this->project = new \stdClass();
        $this->project->name = $input->getArgument('name');
        $this->project->build = $input->getOption('build-folder');
        $this->project->src = $input->getOption('source');
        $this->project->tests = $input->getOption('tests');
        $this->project->excluded = $input->getOption('exclude');
        $this->project->extension = $input->getOption('extension');
        $this->project->resources = $input->getOption('resources');
        
        $buidlXml = $this->twig->render('build.xml.twig', array('project'=>$this->project));
        $output->writeln($buidlXml);
        
        
    }

    /**
     * Set the root directory
     * 
     * @param string $rootDir
     */
    public function setRootDir($rootDir) {
        $this->rootDir = $rootDir;
    }

    private function setupVariables() {
        if (!$this->rootDir) {
            throw new \RuntimeException("Root folder is missing");
        }
        $this->currentDirectory = getcwd();
        $this->resourceFolder = $this->rootDir . '/resources';
        $this->twigCache - $this->rootDir . '/cache';
        $this->initTwig();
    }

    private function initTwig() {
        \Twig_Autoloader::register();

        $loader = new \Twig_Loader_Filesystem($this->resourceFolder);
        $this->twig = new \Twig_Environment($loader, array(
            'cache' => $this->twigCache,
            'strict_variables' => true
        ));
    }

}
