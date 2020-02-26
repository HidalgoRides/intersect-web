<?php

namespace Intersect;

use Intersect\Core\Event;
use Intersect\Core\Container;
use Intersect\Core\Http\Request;
use Intersect\Http\RequestHandler;
use Intersect\Core\Command\Command;
use Intersect\Http\ExceptionHandler;
use Intersect\Core\Http\Router\Route;
use Intersect\Http\Response\Response;
use Intersect\Core\Storage\FileStorage;
use Intersect\Http\Response\TwigResponse;
use Intersect\Http\Response\ViewResponse;
use Intersect\Middleware\MiddlewareStack;
use Intersect\Core\Http\Router\RouteGroup;
use Intersect\Controllers\AbstractController;
use Intersect\Core\Providers\ServiceProvider;
use Intersect\Database\Connection\Connection;
use Intersect\Database\Connection\NullConnection;
use Intersect\Database\Connection\ConnectionFactory;
use Intersect\Database\Connection\ConnectionSettings;
use Intersect\Database\Connection\ConnectionRepository;
use Intersect\Middleware\Middleware;

class Application extends Container {

    private static $CONFIG_DIRECTORY_PATH = '/configs';

    /** @var static */
    private static $INSTANCE;

    /** @var string */
    private $basePath = '';
    private $isInitialized = false;
    private $key;
    private $loadedProviders = [];
    /** @var MiddlewareStack */
    private $middlewareStack;

    public function __construct()
    {
        parent::__construct();
        self::$INSTANCE = $this;

        $this->middlewareStack = new MiddlewareStack();
    }

    public function addMiddleware(Middleware $middleware)
    {
        $this->middlewareStack->add($middleware);
    }

    public function init()
    {
        if ($this->isInitialized)
        {
            return;
        }

        $this->loadConfiguration('config.php');
        $this->loadConfiguration('routes.php', 'routes');

        $applicationKey = $this->getRegisteredConfigs('app.key');
        
        if (is_null($applicationKey) || trim($applicationKey == ''))
        {
            throw new \Exception('Application key not set! Please add the required configuration for "app.key"');
        }

        $this->loadRouteData();
        $this->registerConnections();

        // automatically register app migration path
        $this->migrationPath($this->getMigrationsPath());

        $this->loadProviders();

        $this->key = $applicationKey;
        $this->isInitialized = true;
    }

    /**
     * @return Application
     */
    public static function instance()
    {
        if (is_null(self::$INSTANCE))
        {
            self::$INSTANCE = new static();
        }

        return self::$INSTANCE;
    }

    /**
     * @param $commandKey
     * @param array $data
     */
    public function fireCommand($commandKey, $data = [])
    {
        /** @var Command $registeredCommand */
        $registeredCommand = $this->getCommandRegistry()->get($commandKey);

        if (!is_null($registeredCommand))
        {
            $registeredCommand->execute($data);
        }
    }

    /**
     * @param $eventKey
     * @param array $data
     */
    public function fireEvent($eventKey, $data = [])
    {
        /** @var Event $event */
        $event = $this->eventRegistry->get($eventKey);

        if (!is_null($event))
        {
            $event->handle($data);
        }
    }

    public function getKey()
    {
        return $this->key;
    }

    /**
     * @param $class
     * @param array $namedParameters
     * @return mixed|object
     * @throws \Exception
     */
    public function getClass($class, $namedParameters = [])
    {
        return $this->resolveClass($class, $namedParameters);
    }

    /**
     * @return Connection
     */
    public function getConnection($key = 'default')
    {
        $connection = ConnectionRepository::get($key);

        return (!is_null($connection) ? $connection : new NullConnection());
    }

    public function getBasePath()
    {
        return $this->basePath;
    }

    public function getCachePath()
    {
        return $this->getBasePath() . $this->getRegisteredConfigs('paths.cache');
    }

    public function getConfigsPath()
    {
        return $this->getBasePath() . self::$CONFIG_DIRECTORY_PATH;
    }

    public function getLogsPath()
    {
        return $this->getBasePath() . $this->getRegisteredConfigs('paths.logs');
    }

    public function getMigrationsPath()
    {
        return $this->getBasePath() . $this->getRegisteredConfigs('paths.migrations');
    }

    public function getTemplatesPath()
    {
        return $this->getBasePath() . $this->getRegisteredConfigs('paths.templates');
    }

    public function handleRequest(Request $request)
    {
        $this->init();
        $this->singleton(Request::class, $request);

        /** @var ExceptionHandler $exceptionHandler */
        $exceptionHandler = $this->getClass(ExceptionHandler::class);
        $requestHandler = new RequestHandler($this, $exceptionHandler);

        $requestHandler->setPreInvocationCallback(function($controller) use ($request) {
            if ($controller instanceof AbstractController)
            {
                $controller->setRequest($request);
                $controller->init();
            }
        });

        $response = $requestHandler->handle($request, $this->middlewareStack);

        $this->handleResponse($response, $exceptionHandler);
    }

    public function loadCommands() 
    {
        if (!$this->isInitialized)
        {
            $this->init();
        }
        
        foreach ($this->loadedProviders as $provider => $loaded)
        {
            $providerInstance = new $provider($this);
            $providerInstance->initCommands();
        }
    }

    public function setBasePath($basePath)
    {
        $this->basePath = rtrim($basePath, '/');
    }

    private function getConnectionClosureFromConfiguration($configs)
    {
        return (function() use ($configs) {
            $host = $this->getValue('host', $configs);
            $username = $this->getValue('username', $configs);
            $password = $this->getValue('password', $configs);
            $database = $this->getValue('name', $configs);
            $port = $this->getValue('port', $configs);
            $schema = $this->getValue('schema', $configs);
            $charset = $this->getValue('charset', $configs, 'utf8');
    
            $connectionSettings = ConnectionSettings::builder($host, $username, $password)
                ->port($port)
                ->database($database)
                ->schema($schema)
                ->charset($charset)
                ->build();
    
            return ConnectionFactory::get($configs['driver'], $connectionSettings);
        });
    }

    private function getValue($key, array $data, $defaultValue = null)
    {
        return (array_key_exists($key, $data) ? $data[$key] : (!is_null($defaultValue) ? $defaultValue : null));
    }

    private function handleResponse(Response $response, ExceptionHandler $exceptionHandler = null)
    {
        try {
            if ($response instanceof ViewResponse)
            {
                $response->handle($this->getTemplatesPath());
            }
            else if ($response instanceof TwigResponse)
            {
                $twigConfigs = $this->getRegisteredConfigs('twig');

                if (is_null($twigConfigs))
                {
                    $twigConfigs = [];
                }
                else
                {
                    if (array_key_exists('options', $twigConfigs) && array_key_exists('cache', $twigConfigs['options']))
                    {
                        $cachePath = $twigConfigs['options']['cache'];

                        if (!is_bool($cachePath))
                        {
                            $twigConfigs['options']['cache'] = $this->getCachePath() . '/'. ltrim($cachePath, '/');
                        }
                    }
                }

                $response->handle($this->getTemplatesPath(), $twigConfigs);
            }
            else
            {
                $response->handle();
            }
        } catch (\Exception $e) {
            if (!is_null($exceptionHandler))
            {
                $exceptionResponse = $exceptionHandler->handle($e);
                if (!is_null($exceptionResponse))
                {
                    $this->handleResponse($exceptionResponse);
                }
            }
        }
    }

    private function loadConfiguration($clientFileName, $rootPrefix = null)
    {
        $clientConfigPath = $this->getConfigsPath() . '/' . $clientFileName;
        $this->registerConfigurationFile($clientConfigPath, $rootPrefix);
    }

    private function loadProviders()
    {
        $registeredProviders = $this->getRegisteredConfigs('app.providers');
        if (!is_null($registeredProviders) && is_array($registeredProviders))
        {
            foreach ($registeredProviders as $provider)
            {
                $this->loadProvider($provider);
            }
        }
    }

    private function loadProvider($provider)
    {
        if (array_key_exists($provider, $this->loadedProviders))
        {
            return;
        }

        $providerInstance = new $provider($this);

        if (!$providerInstance instanceof ServiceProvider)
        {
            throw new \Exception('Provider is not an instance of ' . ServiceProvider::class);
        }

        $providerInstance->init();
        $this->loadedProviders[$provider] = true;
    }

    private function loadRouteData()
    {
        $routeConfig = $this->getRegisteredConfigs('routes');

        if (is_null($routeConfig))
        {
            return;
        }

        foreach ($routeConfig as $method => $route)
        {
            if ($route instanceof Route)
            {
                $this->route($route);
            }
            else if ($route instanceof RouteGroup)
            {
                $this->routeGroup($route);
            }
        }
    }

    /**
     * @param $filePath
     * @param null $rootPrefix
     */
    private function registerConfigurationFile($filePath, $rootPrefix = null)
    {
        $fileStorage = FileStorage::getInstance();

        if ($fileStorage->fileExists($filePath))
        {
            $configData = $fileStorage->require($filePath);

            if (!is_null($rootPrefix))
            {
                $configData = [$rootPrefix => $configData];
            }

            $this->getConfigRegistry()->register($configData);
        }
    }

    private function registerConnections()
    {
        $configs = $this->getRegisteredConfigs('database');

        if (is_null($configs))
        {
            return;
        }
        
        if (!is_array($configs) || count($configs) == 0)
        {
            return;
        }

        $connections = $configs['connections'];

        foreach ($connections as $key => $configData)
        {
            ConnectionRepository::register($this->getConnectionClosureFromConfiguration($configData), $key);
        }

        if (array_key_exists('aliases', $configs) && is_array($configs['aliases']))
        {
            $aliases = $configs['aliases'];

            foreach ($aliases as $alias => $key)
            {
                ConnectionRepository::registerAlias($alias, $key);
            }
        }
    }

}