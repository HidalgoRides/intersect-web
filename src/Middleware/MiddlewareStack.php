<?php

namespace Intersect\Middleware;

use Intersect\Core\Http\Request;

class MiddlewareStack {

    private $startFunc;

    public function __construct()
    {
        $this->startFunc = function() {};
    }

    public function add(Middleware $middleware) 
    {
        $nextFunc = $this->startFunc;

        $this->startFunc = function(Request $request) use ($middleware, $nextFunc) {
            return $middleware->run($request, $nextFunc);
        };
    }

    public function execute(Request $request)
    {
        return call_user_func($this->startFunc, $request);
    }

}