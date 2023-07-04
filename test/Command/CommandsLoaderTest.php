<?php

namespace LiteApi\Test\Command;

use LiteApi\Command\CommandHandler;
use PHPUnit\Framework\TestCase;
use LiteApi\Container\Container;
use LiteApi\Test\resources\classes\CommandOne;

/**
 * @covers \LiteApi\Command\CommandsLoaders
 */
class CommandsLoaderTest extends TestCase
{

    /**
     * @covers \LiteApi\Command\CommandHandler::runCommandFromName
     */
    public function testRunCommandFromName()
    {
        $commandsLoader = new CommandHandler();
        $commandsLoader->registerCommand('command:one', CommandOne::class);
        $this->expectOutputString('CommandOne is running');
        $result = $commandsLoader->runCommandFromName('command:one', new Container());
        $this->assertEquals(0, $result);
    }

    /**
     * @covers \LiteApi\Command\CommandHandler::getCommandNameFromServer
     */
    public function testGetCommandNameFromServer()
    {
        $commandsLoader = new CommandHandler();
        $this->assertEquals($_SERVER['argv'][0], $commandsLoader->getCommandNameFromServer());
    }

    /**
     * @covers \LiteApi\Command\CommandHandler::registerCommand
     */
    public function testRegisterCommand()
    {
        $commandsLoader = new CommandHandler();
        $basicCommandsCount = count((new \ReflectionClass($commandsLoader))->getConstant('KERNEL_COMMANDS'));
        $this->assertCount($basicCommandsCount, $commandsLoader->command);
        $commandsLoader->registerCommand('command:one', CommandOne::class);
        $this->assertCount($basicCommandsCount + 1, $commandsLoader->command);
    }
}
