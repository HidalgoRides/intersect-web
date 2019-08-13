<?php

namespace Intersect\Providers;

use Intersect\Application;
use Intersect\Core\Providers\ServiceProvider;

abstract class AppServiceProvider extends ServiceProvider {

    /** @var Application */
    protected $app;

    public function __construct(Application $app)
    {
        parent::__construct($app);
        $this->app = $app;
    }
}