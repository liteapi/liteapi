<?php

namespace LiteApi\Http;

use BackedEnum;
use LiteApi\Exception\ProgrammerException;
use LiteApi\Http\Exception\HttpException;
use LiteApi\Route\QueryType;

/**
 * @phpstan-consistent-constructor
 */
class Request
{

    public const METHOD_HEAD = 'HEAD';
    public const METHOD_GET = 'GET';
    public const METHOD_POST = 'POST';
    public const METHOD_PUT = 'PUT';
    public const METHOD_PATCH = 'PATCH';
    public const METHOD_DELETE = 'DELETE';
    public const METHOD_PURGE = 'PURGE';
    public const METHOD_OPTIONS = 'OPTIONS';
    public const METHOD_TRACE = 'TRACE';
    public const METHOD_CONNECT = 'CONNECT';

    public string $ip;
    public string $path;
    public string $method;
    public ValuesBag $query;
    public ValuesBag $request;
    public ValuesBag $attributes;
    public ServerBag $server;
    public ValuesBag $files;
    public ValuesBag $cookies;
    public HeadersBag $headers;
    private ?string $content;
    public array $parsedContent = [];

    public function __construct(
        array $query = [],
        array $request = [],
        array $cookies = [],
        array $files = [],
        array $server = [],
        array $attributes = [],
        $content = null
    )
    {
        $this->query = new ValuesBag($query);
        $this->request = new ValuesBag($request);
        $this->attributes = new ValuesBag($attributes);
        $this->server = new ServerBag($server);
        $this->files = new ValuesBag($files);
        $this->cookies = new ValuesBag($cookies);
        $this->content = $content;
        
        $this->ip = $this->server->get('REMOTE_ADDR');
        $this->path = $this->server->get('REQUEST_URI');
        $this->method = $this->server->get('REQUEST_METHOD');
        $this->headers = $this->server->getHeaders();
    }

    public static function makeFromGlobals(): static
    {
        return new static($_GET, $_POST, $_COOKIE, $_FILES, $_SERVER);
    }

    /**
     * @param bool $asResource
     * @return string|resource
     */
    public function getContent(bool $asResource = false)
    {
        if ($this->content !== null) {
            if ($asResource === true) {
                $resource = fopen('php://temp', 'r+');
                fwrite($resource, $this->content);
                rewind($resource);
                return $resource;
            } else {
                return $this->content;
            }
        }
        if ($asResource === true) {
            return fopen('php://input', 'r');
        }
        $this->content = file_get_contents('php://input');
        return $this->content;
    }

    public function getRequestLogMessage(): string
    {
        return sprintf('Requested path: %s, with %s method from ip: %s', $this->path, $this->method, $this->ip);
    }

    /**
     * @param array<string,string|QueryType> $queryDefinitions
     * @return void
     * @throws HttpException
     * @throws ProgrammerException
     */
    public function parseQueryByDefinition(array $queryDefinitions): void
    {
        $queryParams = $this->query->values;
        foreach ($queryDefinitions as $key => $definition) {
            if (!isset($queryParams[$key])) {
                continue;
            }
            $value = $queryParams[$key];
            if ($definition instanceof QueryType) {
                $queryParams[$key] = $definition->convertValue($value);
            } elseif (is_subclass_of($definition, BackedEnum::class)) {
                $enumValue = $definition::tryFrom($value);
                if ($enumValue === null) {
                    $cases = [];
                    foreach ($definition::cases() as $case) {
                        $cases[] = $case->value;
                    }
                    throw new HttpException(ResponseStatus::BadRequest,
                        sprintf('%s is not valid value, choose from %s', $value, implode(', ', $cases)));
                }
            } else {
                throw new ProgrammerException(sprintf('Unsupported type of definition: %s of key %s', $definition, $key));
            }
        }

    }

    /**
     * @param string[] $requiredParams
     * @return void
     * @throws HttpException
     */
    public function parseJsonContent(array $requiredParams): void
    {
        $content = $this->getContent();
        if (!is_string($content)) {
            throw new HttpException(ResponseStatus::BadRequest, 'Content is not a valid string json');
        }
        $this->parsedContent = json_decode($content, true);
        if ($this->parsedContent === false) {
            throw new HttpException(ResponseStatus::BadRequest, 'Content is not valid json');
        }
        $keyDiff = array_diff($requiredParams, array_keys($this->parsedContent));
        if (!empty($keyDiff)) {
            throw new HttpException(ResponseStatus::BadRequest,
                'Missing required params: ' . implode(', ', $keyDiff));
        }
    }

}