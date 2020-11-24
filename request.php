<?php

class SimpleJsonRequest
{
    private static function makeCache(string $url, array $opts)
    {
        $redis = (new Name\Space\Redis())->connect($_ENV('REDIS_HOSTNAME'), $_ENV('REDIS_PORT'));

        $key = "{$opts['http']['method']}_$url";

        if ( $redis->exists($key) ) {
            return $redis->get($key);
        }

        $result = file_get_contents($url, false, stream_context_create($opts));

        $redis->setEx($key, 3600, $result);

        return $result;
    }

    private static function makeRequest(string $method, string $url, array $parameters = null, array $data = null)
    {
        $opts = [
            'http' => [
                'method'  => $method,
                'header'  => 'Content-type: application/json',
                'content' => $data ? json_encode($data) : null
            ]
        ];

        if ( $parameters ) {
            ksort($parameters);
            $url .= http_build_query($parameters);
        }
        
        return is_null($data) ? self::makeCache($url, $opts) : file_get_contents($url, false, stream_context_create($opts));
    }

    public static function get(string $url, array $parameters = null)
    {
        return json_decode(self::makeRequest('GET', $url, $parameters));
    }

    public static function post(string $url, array $parameters = null, array $data)
    {
        return json_decode(self::makeRequest('POST', $url, $parameters, $data));
    }

    public static function put(string $url, array $parameters = null, array $data)
    {
        return json_decode(self::makeRequest('PUT', $url, $parameters, $data));
    }   

    public static function patch(string $url, array $parameters = null, array $data)
    {
        return json_decode(self::makeRequest('PATCH', $url, $parameters, $data));
    }

    public static function delete(string $url, array $parameters = null, array $data = null)
    {
        return json_decode(self::makeRequest('DELETE', $url, $parameters, $data));
    }
}