<?php

namespace Intersect\Providers;

class CorsProvider extends AppServiceProvider {

    private static $KEY_ALLOW_CREDENTIALS = 'allow-credentials';
    private static $KEY_ALLOW_HEADERS = 'allow-headers';
    private static $KEY_ALLOW_METHODS = 'allow-methods';
    private static $KEY_ALLOW_ORIGIN = 'allow-origin';
    private static $KEY_EXPOSE_HEADERS = 'expose-headers';
    private static $KEY_MAX_AGE = 'max-age';

    public function init()
    {
        $corsConfig = $this->app->getConfigRegistry()->get('app.cors');

        if (is_null($corsConfig) || !is_array($corsConfig))
        {
            return;
        }

        if (array_key_exists(self::$KEY_ALLOW_CREDENTIALS, $corsConfig))
        {
            header('Access-Control-Allow-Credentials: ' . $corsConfig[self::$KEY_ALLOW_CREDENTIALS]);
        }

        if (array_key_exists(self::$KEY_ALLOW_ORIGIN, $corsConfig))
        {
            header('Access-Control-Allow-Origin: ' . $corsConfig[self::$KEY_ALLOW_ORIGIN]);
        }

        if (array_key_exists(self::$KEY_ALLOW_HEADERS, $corsConfig))
        {
            $allowHeaders = $corsConfig[self::$KEY_ALLOW_HEADERS];
            if (is_array($allowHeaders) && count($allowHeaders) > 0) 
            {
                header('Access-Control-Allow-Headers: ' . implode(',', $allowHeaders));
            }
        }

        if (array_key_exists(self::$KEY_ALLOW_METHODS, $corsConfig))
        {
            $allowMethods = $corsConfig[self::$KEY_ALLOW_METHODS];
            if (is_array($allowMethods) && count($allowMethods) > 0) 
            {
                header('Access-Control-Allow-Methods: ' . implode(',', $allowMethods));
            }
        }

        if (array_key_exists(self::$KEY_EXPOSE_HEADERS, $corsConfig))
        {
            $exposeHeaders = $corsConfig[self::$KEY_EXPOSE_HEADERS];
            if (is_array($exposeHeaders) && count($exposeHeaders) > 0) 
            {
                header('Access-Control-Expose-Headers: ' . implode(',', $exposeHeaders));
            }
        }

        if (array_key_exists(self::$KEY_MAX_AGE, $corsConfig))
        {
            $maxAge = $corsConfig[self::$KEY_MAX_AGE];
            if (!is_null($maxAge))
            {
                header('Access-Control-Max-Age: ' . (int) $maxAge);
            }
        }
    }
}