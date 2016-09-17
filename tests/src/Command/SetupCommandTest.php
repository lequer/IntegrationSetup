<?php

namespace MLequer\Command;

use Symfony\Component\Console\Application;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;
use MLequer\Command\SetupCommand;
/**
 * Generated by PHPUnit_SkeletonGenerator on 2016-09-16 at 21:41:19.
 */
class SetupCommandTest extends \PHPUnit_Framework_TestCase {

    private $tester;
    private $command;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp() {
        $application = new Application();
        $setupCommand = new SetupCommand();
        $setupCommand->setRootDir(__DIR__.'/../../../');
        $application->add($setupCommand);

        $this->command = $application->find('setup');
        $this->tester = new CommandTester($this->command);
    }

    public function testDryRunExecute() {
        $this->tester->execute(array(
            'command' => $this->command->getName(),
            '--dry-run' => true,
            'name' => "Test Project"
        ));
        $output = $this->tester->getDisplay();
        $this->assertContains('Test Project', $output);
        $this->assertContains('<target name="sonar"', $output);
    }


}
