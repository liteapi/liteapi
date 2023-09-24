<?php

namespace LiteApi\Http\Request;

use LiteApi\Http\Exception\HttpException;
use LiteApi\Http\Response\ResponseStatus;

class RequestHelper
{

    /**
     * @param Request|string $request
     * @param array $requiredKeys
     * @return array
     * @throws HttpException
     */
    public static function parseJson(Request|string $request, array $requiredKeys = []): array
    {
        $content = $request instanceof Request ? $request->getContent() : $request;
        $content = json_decode($content, true);
        if ($content === false) {
            throw new HttpException(ResponseStatus::BadRequest, 'Content is not valid json');
        }
        $keyDiff = array_diff($requiredKeys, array_keys($content));
        if (!empty($keyDiff)) {
            throw new HttpException(ResponseStatus::BadRequest,
                'Missing required keys: ' . implode(', ', $keyDiff));
        }
        return $content;
    }
}