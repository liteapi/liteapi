<?php

namespace LiteApi\Command\Input;

use Exception;
use LiteApi\Exception\KernelException;

class Stdin implements InputInterface
{

    /**
     * @var Option[]
     */
    public array $options = [];
    /**
     * @var Argument[]
     */
    public array $arguments = [];

    /**
     * @return void
     * @throws KernelException
     * @throws Exception
     */
    public function load(): void
    {
        $loadedArguments = [];
        $loadedOptions = [];
        $argv = $_SERVER['argv'];
        $argvCount = count($argv);
        for ($i = 2; $i <= $argvCount; $i++) {
            if (!isset($argv[$i])) {
                continue;
            }
            if (str_contains($argv[$i], '=')) {
                [$optionName, $value] = explode('=', $argv[$i], 2);
                if (empty($value)) {
                    throw new Exception('Missing value for option: ' . $optionName);
                }
                if (str_starts_with($optionName, '--')) {
                    $loadedOptions[] = [
                        'shortName' => null,
                        'longName' => substr($optionName, 2),
                        'value' => $value
                    ];
                } elseif (str_starts_with($optionName, '-')) {
                    $loadedOptions[] = [
                        'shortName' => substr($optionName, 1),
                        'longName' => null,
                        'value' => $value
                    ];
                } else {
                    throw new KernelException('Uncovered option ' . $argv[$i]);
                }
            } elseif (str_starts_with($argv[$i], '--')) {
                if (!isset($argv[$i+1])) {
                    throw new Exception('Missing value for option: ' . substr($argv[$i], 2));
                }
                $loadedOptions[] = [
                    'shortName' => null,
                    'longName' => substr($argv[$i], 2),
                    'value' => $argv[$i+1]
                ];
                unset($argv[$i+1]);
            } elseif (str_starts_with($argv[$i], '-')) {
                if (!isset($argv[$i+1])) {
                    throw new Exception('Missing value for option: ' . substr($argv[$i], 1));
                }
                $loadedOptions[] = [
                    'shortName' => substr($argv[$i], 1),
                    'longName' => null,
                    'value' => $argv[$i+1]
                ];
                unset($argv[$i+1]);
            } else {
                $loadedArguments[] = $argv[$i];
            }
        }
        foreach ($this->arguments as $argument) {
            $argument->setValue(array_shift($loadedArguments));
            if ($argument->type === Argument::REQUIRED && $argument->value === null) {
                throw new Exception('Missing required argument: ' . $argument->name);
            }
        }
        foreach ($this->options as $option) {
            $loadedOption = $this->optionExists($loadedOptions, $option->shortcut, $option->name);
            if ($loadedOption !== false) {
                $option->setValue($loadedOption['value']);
            }
            if ($option->type === Option::REQUIRED && $option->value === null) {
                throw new Exception('Missing required option: ' . $option->name);
            }
        }
    }

    /**
     * @param array $options
     * @param string $shortName
     * @param string $longName
     * @return array|false
     */
    private function optionExists(array $options, string $shortName, string $longName): array|false
    {
        foreach ($options as $option) {
            if ($shortName === $option['shortName']) {
                return $option;
            }
            if ($longName === $option['longName']) {
                return $option;
            }
        }
        return false;
    }

    public function addOption(
        string $name,
        ?string $shortcut = null,
        int $type = Option::OPTIONAL,
        mixed $default = null,
        string $description = null
    ): void
    {
        $this->options[] = new Option($name, $shortcut, $type, $default, $description);
    }

    public function addArgument(
        string $name,
        int $type = Argument::REQUIRED,
        string $description = null
    ): void
    {
        $this->arguments[] = new Argument($name, $type, $description);
    }

    /**
     * @param string $name
     * @return int|string|null
     * @throws Exception
     */
    public function getOption(string $name): int|string|null
    {
        foreach ($this->options as $option) {
            if ($option->name === $name) {
                return $option->value ?? $option->default;
            }
        }
        throw new Exception("Option $name wasn't found");
    }

    /**
     * @param string $name
     * @return int|string|null
     * @throws Exception
     */
    public function getArgument(string $name): int|string|null
    {
        foreach ($this->arguments as $argument) {
            if ($argument->name === $name) {
                return $argument->value;
            }
        }
        throw new Exception("Argument $name wasn't found");
    }
}