<?php

namespace Intersect\Middleware;

use Intersect\Core\Http\Request;

interface Middleware {

    public function run(Request $request, callable $next);

}