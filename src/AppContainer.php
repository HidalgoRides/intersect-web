<?php

namespace Intersect;

use Intersect\Core\Container;
use Intersect\Http\Router\RouteRegistry;

class AppContainer extends Container {

    /** @var RouteRegistry */
    private $routeRegistry;

    public function __construct()
    {
        parent::__construct();
        $this->routeRegistry = new RouteRegistry();
    }

    /**
     * @return RouteRegistry
     */
    public function getRouteRegistry()
    {
        return $this->routeRegistry;
    }

}