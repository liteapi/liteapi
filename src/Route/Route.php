<?php

namespace LiteApi\Route;

use Exception;
use LiteApi\Component\Common\BuiltinValue;
use LiteApi\Container\Awareness\ContainerAwareInterface;
use LiteApi\Container\Container;
use LiteApi\Container\ContainerNotFoundException;
use LiteApi\Exception\KernelException;
use LiteApi\Exception\ProgrammerException;
use LiteApi\Http\Request;
use LiteApi\Http\Response;
use LiteApi\Route\Attribute\HasJsonContent;
use LiteApi\Route\Attribute\HasQuery;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionClass;
use ReflectionNamedType;

class Route
{

    private string $className;
    private string $methodName;
    public string $path;
    public array $httpMethods;
    public ?string $regexPath = null;

    public function __construct(string $className, string $methodName, string $path, array $httpMethods)
    {
        $this->className = $className;
        $this->methodName = $methodName;
        $this->path = $path;
        $this->httpMethods = $httpMethods;
    }

    /**
     * @return void
     * @throws \ReflectionException
     * @throws Exception
     */
    public function makeRegexPath(): void
    {
        $this->regexPath = $this->path;
        preg_match_all('/\{\w+\}/', $this->path, $pathParameters, PREG_SET_ORDER);
        $this->regexPath = '/^' . str_replace('/', '\\/', $this->regexPath) . '$/';
        if (empty($pathParameters)) {
            return;
        }
        $pathParameters = array_map(
            function($param) {
                return substr($param[0], 1, strlen($param[0])-2);
            },
            $pathParameters
        );
        $reflectionMethod = (new ReflectionClass($this->className))->getMethod($this->methodName);
        foreach ($reflectionMethod->getParameters() as $parameter) {
            if (($parameterIndex = array_search($parameter->getName(), $pathParameters, true)) !== false) {
                /** @var \ReflectionNamedType $parameterType */
                $parameterType = $parameter->getType();
                if ($parameterType->isBuiltin()) {
                    $parameterTypeName = $parameterType->getName();
                    if ($parameterTypeName === BuiltinValue::String->value) {
                        $this->regexPath = str_replace('{' . $pathParameters[$parameterIndex] . '}', '(\w+)', $this->regexPath);
                    } elseif ($parameterTypeName === BuiltinValue::Int->value) {
                        $this->regexPath = str_replace('{' . $pathParameters[$parameterIndex] . '}', '(\d+)', $this->regexPath);
                    } else {
                        throw new ProgrammerException(sprintf('Not supported builtin type %s for parameter %s', $parameterTypeName, $parameter->getName()));
                    }
                    //$this->pathParams[] = $pathParameters[$parameterIndex];
                    unset($pathParameters[$parameterIndex]);
                } else {
                    throw new ProgrammerException('Invalid parameter type ' . $parameterType->getName());
                }
            }
        }
        if (!empty($pathParameters)) {
            throw new ProgrammerException('Missing value for :' . implode(', ', $pathParameters));
        }
        if ($this->regexPath === '') {
            $this->regexPath = '/';
        }
    }

    /**
     * @param Container $container
     * @param Request $request
     * @return Response
     * @throws ProgrammerException|Exception
     */
    public function execute(Container $container, Request $request): Response
    {
        try {
            $reflectionClass = new ReflectionClass($this->className);
            $constructor = $reflectionClass->getConstructor();
            $constructorArgs = [];
            if ($constructor != null) {
                $constructorArgs = $this->loadArguments($constructor->getParameters(), $container, $request); //, false
            }
            $class = $reflectionClass->newInstanceArgs($constructorArgs);

            $reflectionMethod = $reflectionClass->getMethod($this->methodName);
            $args = $this->loadArguments($reflectionMethod->getParameters(), $container, $request); //, true

            $hasQueryAttributes = $reflectionMethod->getAttributes(HasQuery::class);
            if (!empty($hasQueryAttributes)) {
                $request->parseQueryByDefinition($hasQueryAttributes[0]->getArguments()[0]);
            }

            $hasJsonContentAttributes = $reflectionMethod->getAttributes(HasJsonContent::class);
            if (!empty($hasJsonContentAttributes)) {
                $request->parseJsonContent($hasJsonContentAttributes[0]->getArguments()[0]);
            }

            if (is_subclass_of($class, ContainerAwareInterface::class)) {
                $class->setContainer($container);
            }
        } catch (Exception $e) {
            throw new ProgrammerException('Error while loading route, see previous exception', previous: $e);
        }

        $result = $reflectionMethod->invokeArgs($class, $args);
        if ($result instanceof Response) {
            return $result;
        } elseif (is_array($result)) {
            return new Response($result);
        } elseif (is_string($result)) {
            return new Response($result);
        } else {
            throw new ProgrammerException('Invalid object type of response: ' . var_export($result, true));
        }
    }

    /**
     * @param \ReflectionParameter[] $parameters
     * @param Container $container
     * @param Request $request
     * @return array
     * @throws KernelException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     * @throws ContainerNotFoundException
     */
    private function loadArguments(array $parameters, Container $container, Request $request): array
    {
        $pathParameters = [];
        if ($this->regexPath !== null && preg_match($this->regexPath, $request->path, $pathParameters) === false) {
            throw new KernelException('Cannot math regex path to requested path while loading parameters');
        }
        array_shift($pathParameters);
        $pathParameterIndex = 0;
        $args = [];
        foreach ($parameters as $parameter) {
            /** @var ReflectionNamedType $type */
            $type = $parameter->getType();
            if ($type instanceof ReflectionNamedType && !$type->isBuiltin()) {
                if ($type->getName() === Request::class) {
                    $args[] = $request;
                } elseif ($type->getName() === Response::class) {
                    $args[] = new Response();
                } else {
                    $args[] = $container->get($type->getName());
                }
            } else {
                $enumType = BuiltinValue::from($type->getName());
                $args[] = $enumType->convertValue($pathParameters[$pathParameterIndex]);
                $pathParameterIndex++;
            }
        }
        return $args;
    }

    public function __serialize(): array
    {
        return [
            $this->className,
            $this->methodName,
            $this->path,
            $this->httpMethods,
            $this->regexPath
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->className = $data[0];
        $this->methodName = $data[1];
        $this->path = $data[2];
        $this->httpMethods = $data[3];
        $this->regexPath = $data[4];
    }
}