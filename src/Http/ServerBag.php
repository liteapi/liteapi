<?php

namespace LiteApi\Http;

class ServerBag extends ValuesBag
{

    public function getHeaders(): HeadersBag
    {
        $headers = [];
        foreach ($this->values as $key => $value) {
            if (str_starts_with($key, 'HTTP_')) {
                $headers[substr($key, 5)] = $value;
            } elseif (in_array($key, ['CONTENT_TYPE', 'CONTENT_LENGTH', 'CONTENT_MD5'], true)) {
                $headers[$key] = $value;
            }
        }
        return new HeadersBag($headers);
    }

}