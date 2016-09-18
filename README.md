[![Build Status](https://travis-ci.org/lequer/PHP-Project-setup.svg?branch=master)](https://travis-ci.org/lequer/PHP-Project-setup)

# Integration Setup

This is the tool I use to setup my projects. This allows for setup consistency across multiple projects.
It uses Phing and the setup is based on [Template for Jenkins Jobs for PHP Projects](http://jenkins-php.org/index.html)

The script adds `build.xml`, phpunit configuration file, phpmd setup, sonarQube setup and documentation generation with [Sami](https://github.com/FriendsOfPHP/Sami).

Currently, It does not support external template folder


## Installation

Install the latest version with

`$ composer require mlequer/integration-setup`


## Usage

```
Usage:
  setup [options] [--] <name>

Arguments:
  name                                 Project name

Options:
  -d, --destination=DESTINATION        Relative destination for build.xml [default: "."]
  -b, --build-folder=BUILD-FOLDER      Build folder [default: "build"]
  -r, --resources=RESOURCES            Destination folder for the resources [default: "Resources"]
  -s, --source=SOURCE                  Source folder [default: "src"]
  -t, --tests=TESTS                    Tests folder [default: "tests"]
  -c, --configuration[=CONFIGURATION]  configuration file
  -e, --exclude=EXCLUDE                Exclude folders [default: ["vendor","build"]] (multiple values allowed)
  -x, --extensions=EXTENSIONS          File extensions [default: ["php"]] (multiple values allowed)
      --dry-run                        Dry run
      --skip-composer                  Skip composer update
  -g, --generate-config                Generate config from parameters
  -h, --help                           Display this help message
  -q, --quiet                          Do not output any message
  -V, --version                        Display this application version
      --ansi                           Force ANSI output
      --no-ansi                        Disable ANSI output
  -n, --no-interaction                 Do not ask any interactive question
  -v|vv|vvv, --verbose                 Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
```

Or use a yaml config file:

`$ bin/console setup PHPCISetup -c .ci-setup.yml`
