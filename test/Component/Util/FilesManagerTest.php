<?php

namespace LiteApi\Test\Component\Util;

use LiteApi\Component\Util\FilesManager;
use PHPUnit\Framework\TestCase;

class FilesManagerTest extends TestCase
{

    /**
     * @covers \LiteApi\Component\Util\FilesManager::getClassesNamesFromPath
     */
    public function testGetClassesNamesFromPath(): void
    {
        $testDir = __DIR__ . '/../../Command/';
        $filesManager = new FilesManager();
        $classes = $filesManager->getClassesNamesFromPath($testDir, 'LiteApi\\Test\\Command');
        $this->assertIsArray($classes);
        $this->assertCount(2, $classes);
        sort($classes);
        $expectedClasses = ['LiteApi\\Test\\Command\\CommandsLoaderTest', 'LiteApi\\Test\\Command\\Input\\StdinTest'];
        sort($expectedClasses);
        $this->assertEquals($expectedClasses, $classes);
    }
}
