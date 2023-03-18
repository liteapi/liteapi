<?php

namespace LiteApi\Test\Command;

use LiteApi\Command\CommandsLoader;
use PHPUnit\Framework\TestCase;
use LiteApi\Container\ContainerLoader;
use LiteApi\Test\resources\classes\CommandOne;

/**
 * @covers \LiteApi\Command\CommandsLoaders
 */
class CommandsLoaderTest extends TestCase
{

    /**
     * @covers \LiteApi\Command\CommandsLoader::runCommandFromName
     */
    public function testRunCommandFromName()
    {
        $commandsLoader = new CommandsLoader();
        $commandsLoader->registerCommand('command:one', CommandOne::class);
        $this->expectOutputString('CommandOne is running');
        $result = $commandsLoader->runCommandFromName('command:one', new ContainerLoader());
        $this->assertEquals(0, $result);
    }

    /**
     * @covers \LiteApi\Command\CommandsLoader::getCommandNameFromServer
     */
    public function testGetCommandNameFromServer()
    {
        $commandsLoader = new CommandsLoader();
        $this->assertEquals($_SERVER['argv'][0], $commandsLoader->getCommandNameFromServer());
    }

    /**
     * @covers \LiteApi\Command\CommandsLoader::registerCommand
     */
    public function testRegisterCommand()
    {
        $commandsLoader = new CommandsLoader();
        $basicCommandsCount = count((new \ReflectionClass($commandsLoader))->getConstant('KERNEL_COMMANDS'));
        $this->assertCount($basicCommandsCount, $commandsLoader->command);
        $commandsLoader->registerCommand('command:one', CommandOne::class);
        $this->assertCount($basicCommandsCount + 1, $commandsLoader->command);
    }
}
