<?php

namespace LiteApi\Component\Extension;

use Exception;
use LiteApi\Command\CommandHandler;
use LiteApi\Component\Common\ArrayAssertionTrait;
use LiteApi\Component\Util\PhpArrayExporter;
use LiteApi\Container\Container;
use LiteApi\Http\Router;

class Extension
{

    use ArrayAssertionTrait;

    protected array $config = [];

    public function loadConfig(array $config): void
    {
        $this->config = $config;
    }

    public function validateConfig(): void
    {
    }

    public function registerServices(Container $container): void
    {
    }

    public function registerRoutes(Router $router): void
    {
    }

    public function registerCommands(CommandHandler $commandHandler): void
    {
    }

    public function loadFiles(string $projectDir): void
    {

    }

    protected function putFile(string $path, string|array $content, bool $overwrite = false, int $flags = 0): void
    {
        if (file_exists($path) && !$overwrite) {
            return;
        }
        $content = is_array($content) ? PhpArrayExporter::export($content) : $content;
        if (file_put_contents($path, $content, $flags) === false) {
            throw new Exception(sprintf('Cannot write to path: %s, error %s. Content: %s',
                $path, error_get_last()['message'] ?? '', $content));
        }
    }
}