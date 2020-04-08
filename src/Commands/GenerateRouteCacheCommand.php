<?php

namespace Intersect\Commands;

use Intersect\Application;
use Intersect\Core\Logger\Logger;
use Intersect\Core\Command\Command;
use Intersect\Core\Http\Router\Route;
use Intersect\Core\Http\Router\RouteAction;
use Intersect\Core\Storage\FileStorage;
use Intersect\Core\Logger\ConsoleLogger;

class GenerateRouteCacheCommand implements Command {

    /** @var Application */
    private $app;
    /** @var FileStorage */
    private $fileStorage;
    /** @var Logger */
    private $logger;

    public function __construct()
    {
        $this->app = Application::instance();
        $this->fileStorage = new FileStorage();
        $this->logger = new ConsoleLogger();
    }

    public function execute($data = [])
    {
        $allRoutes = $this->app->getRouteRegistry()->getAll();

        $routeCacheData = [];

        foreach ($allRoutes as $method => $routes)
        {
            if (!array_key_exists($method, $routeCacheData))
            {
                $routeCacheData[$method] = [];
            }

            /** @var Route $route */
            foreach ($routes as $route)
            {
                $routeAction = RouteAction::fromRoute($route);

                if ($routeAction->getIsCallable())
                {
                    $this->logger->error('Cannot generate the route cache because one of your routes uses closures.');
                    return;
                }

                $routeCacheData[$method][] = [
                    'name' => $route->getName(),
                    'path' => $route->getPath(),
                    'controller' => $routeAction->getController(),
                    'method' => $routeAction->getMethod(),
                    'extras' => $routeAction->getExtraOptions()
                ];
            }
        }

        $cachePath = $this->app->getCachePath();

        if (!$this->fileStorage->directoryExists($cachePath))
        {
            $this->fileStorage->writeDirectory($cachePath);
        }

        $filePath = $cachePath . '/routes.php';
        $this->fileStorage->writeFile($filePath, json_encode($routeCacheData));
        
        $this->logger->info('Route cache generated at ' . $filePath);
    }

    public function getDescription()
    {
        return 'Generates list of cached routes.';
    }
    
    public function getParameters()
    {
        return [];
    }

}