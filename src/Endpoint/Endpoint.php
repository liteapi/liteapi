<?php

namespace pjpawel\LightApi\Endpoint;

use pjpawel\LightApi\Container\ContainerLoader;
use pjpawel\LightApi\Http\Exception\HttpException;
use pjpawel\LightApi\Http\Request;
use pjpawel\LightApi\Http\Response;
use pjpawel\LightApi\Http\ResponseStatus;
use pjpawel\LightApi\Kernel\KernelException;
use pjpawel\LightApi\Kernel\ProgrammerException;
use ReflectionClass;
use ReflectionNamedType;

class Endpoint
{

    private string $className;
    private string $methodName;
    public string $path;
    public array $httpMethods;
    public string $regexPath;

    public function __construct(string $className, string $methodName, string $path, array $httpMethods)
    {
        $this->className = $className;
        $this->methodName = $methodName;
        $this->path = $path;
        $this->httpMethods = $httpMethods;
        $this->makeRegexPath();
    }

    /**
     * @return void
     * @throws \ReflectionException
     * @throws \Exception
     */
    private function makeRegexPath(): void
    {
        $this->regexPath = $this->path;
        preg_match_all('/\{\w+\}/', $this->path, $pathParameters, PREG_SET_ORDER);
        $this->regexPath = '/^' . str_replace('/', '\\/', $this->regexPath) . '/';
        if (empty($pathParameters)) {
            return;
        }
        $pathParameters = array_map(
            function ($param) {
                return substr($param[0], 1, strlen($param[0])-2);
            },
            $pathParameters
        );
        $reflectionMethod = (new ReflectionClass($this->className))->getMethod($this->methodName);
        foreach ($reflectionMethod->getParameters() as $parameter) {
            if (($parameterIndex = array_search($parameter->getName(), $pathParameters, true)) !== false) {
                $parameterType = $parameter->getType();
                if ($parameterType->isBuiltin()) {
                    $parameterTypeName = $parameterType->getName();
                    if ($parameterTypeName == 'string') {
                        $this->regexPath = str_replace('{' . $pathParameters[$parameterIndex] . '}', '(\w+)', $this->regexPath);
                    } elseif ($parameterTypeName == 'int') {
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
    }

    /**
     * @param ContainerLoader $container
     * @param Request $request
     * @return Response
     * @throws \ReflectionException
     * @throws \pjpawel\LightApi\Container\ContainerNotFoundException
     * @throws \Exception
     */
    public function execute(ContainerLoader $container, Request $request): Response
    {
        try {
            $reflectionClass = new ReflectionClass($this->className);
            $constructor = $reflectionClass->getConstructor();
            $constructorArgs = [];
            if ($constructor != null) {
                $constructorArgs = $this->loadArguments($constructor->getParameters(), $container, $request, false);
            }
            $class = $reflectionClass->newInstanceArgs($constructorArgs);

            $reflectionMethod = $reflectionClass->getMethod($this->methodName);
            $args = $this->loadArguments($reflectionMethod->getParameters(), $container, $request, true);

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
        } catch (HttpException $httpExc) {
            return new Response($httpExc->getMessage(), ResponseStatus::from($httpExc->getCode()));
        } catch (\Exception $exc) {
            return new Response('Internal server error occurred', ResponseStatus::INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param \ReflectionParameter[] $parameters
     * @param ContainerLoader $container
     * @param Request $request
     * @param bool $allowedPathArguments
     * @return array
     * @throws \ReflectionException
     * @throws \pjpawel\LightApi\Container\ContainerNotFoundException
     * @throws KernelException
     */
    private function loadArguments(array $parameters, ContainerLoader $container, Request $request, bool $allowedPathArguments): array
    {
        if (preg_match($this->regexPath, $request->path, $pathParameters) === false) {
            throw new KernelException('Cannot math regexpath to requested path while loading parameters');
        }
        array_shift($pathParameters);
        $pathParameterIndex = 0;
        $args = [];
        foreach ($parameters as $parameter) {
            $type = $parameter->getType();
            if ($type instanceof ReflectionNamedType && !$type->isBuiltin()) {
                if ($type->getName() == Request::class) {
                    $args[] = $request;
                } elseif ($type->getName() == Response::class) {
                    $args[] = new Response();
                } else {
                    $args[] = $container->get($type->getName());
                }
            } else {
                if ($type->getName() == 'int') {
                    $args[] = (int) $pathParameters[$pathParameterIndex];
                } else {
                    $args[] = $pathParameters[$pathParameterIndex];
                }
                $pathParameterIndex++;
            }
        }
        return $args;
    }
}