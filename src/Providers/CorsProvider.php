<?php

namespace Intersect\Providers;

class CorsProvider extends AppServiceProvider {

    public function init()
    {
        $corsConfig = $this->app->getConfigRegistry()->get('app.cors');

        if ($corsConfig['enabled'])
        {
            header('Access-Control-Allow-Credentials: ' . $corsConfig['allow-credentials']);
            header('Access-Control-Allow-Origin: ' . $corsConfig['allow-origin']);

            $allowHeaders = $corsConfig['allow-headers'];
            if (count($allowHeaders) > 0) 
            {
                header('Access-Control-Allow-Headers: ' . implode(',', $allowHeaders));
            }

            $allowMethods = $corsConfig['allow-methods'];
            if (count($allowMethods) > 0) 
            {
                header('Access-Control-Allow-Methods: ' . implode(',', $allowMethods));
            }

            $exposeHeaders = $corsConfig['expose-headers'];
            if (count($exposeHeaders) > 0) 
            {
                header('Access-Control-Expose-Headers: ' . implode(',', $exposeHeaders));
            }

            $maxAge = $corsConfig['max-age'];
            if (!is_null($maxAge))
            {
                header('Access-Control-Max-Age: ' . (int) $maxAge);
            }
        }
    }
}