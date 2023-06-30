<?php

namespace LiteApi\Http;

use LiteApi\Http\Exception\HttpException;

class RequestHelper
{

    /**
     * @param Request $request
     * @param array $requiredParameters
     * @return array
     * @throws HttpException
     */
    public static function parseJson(Request $request, array $requiredParameters = []): array
    {
        $content = json_decode($request->getContent(), true);
        foreach ($requiredParameters as $requiredParameter) {
            if (!isset($content[$requiredParameter])) {
                throw new HttpException(ResponseStatus::BadRequest, 'Missing required parameter ' . $requiredParameter);
            }
        }
        return $content;
    }
}