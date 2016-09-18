<?php

/*
 * Copyright (c) Michel Le Quer
 * All rights reserved.
 */

namespace MLequer\Entity;

use Symfony\Component\Yaml\Yaml;

/**
 * Description of Project
 *
 * @author michel
 */
class Project
{

    public $name;
    public $key;
    public $buildFolder;
    public $source;
    public $tests;
    public $excluded = [];
    public $extensions = [];
    public $resources;
    private $parser;

    public function __construct($name, array $options, $parser = Yaml::class)
    {
        $this->name = $name;
        $this->parser = $parser;
        if (null !== $options['configuration']) {
            $options = $this->loadFromConfigFile($options['configuration']);
        }
        $this->loadFromOptions($options);
    }

    private function loadFromOptions($options)
    {
        $this->key = $this->makeKey($this->name);
        $this->build = $options['build-folder'];
        $this->src = $options['source'];
        $this->tests = $options['tests'];
        $this->excluded = $options['exclude'];
        $this->extensions = $options['extensions'];
        $this->resources = $options['resources'];
    }

    private function loadFromConfigFile($filePath)
    {
        $options = call_user_func(array($this->parser, 'parse'), file_get_contents($filePath));
        $this->name = $options['name'];
        unset($options['name']);
        
        return $options;
    }

    private function makeKey($string)
    {
        return strtolower(preg_replace(['/([a-z\d])([A-Z])/', '/([^_])([A-Z][a-z])/'], '$1:$2', $string));
    }
}
