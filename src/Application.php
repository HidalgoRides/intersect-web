<?php

namespace Intersect;

use Closure;
use Intersect\Core\Event;
use Intersect\AppContainer;
use Intersect\Core\Http\Request;
use Intersect\Http\Router\Route;
use Intersect\Core\Http\Response;
use Intersect\Core\MethodInvoker;
use Intersect\Core\ClosureInvoker;
use Intersect\Http\RequestHandler;
use Intersect\Core\Command\Command;
use Intersect\Http\ExceptionHandler;
use Intersect\Core\ParameterResolver;
use Intersect\Http\Router\RouteGroup;
use Intersect\Core\Storage\FileStorage;
use Intersect\Core\Http\ResponseHandler;
use Intersect\Http\Router\RouteRegistry;
use Intersect\Database\Connection\Connection;
use Intersect\Database\Connection\NullConnection;
use Intersect\Database\Connection\ConnectionFactory;
use Intersect\Database\Connection\ConnectionSettings;
use Intersect\Database\Response\ModelResponseHandler;
use Intersect\Database\Connection\ConnectionRepository;
use Intersect\Http\Response\Handlers\TwigResponseHandler;
use Intersect\Http\Response\Handlers\ViewResponseHandler;
use Intersect\Http\Response\Handlers\ArrayResponseHandler;
use Intersect\Http\Response\Handlers\StringResponseHandler;
use Intersect\Http\Response\Handlers\XmlResponseHandler;
use Intersect\Http\Response\Handlers\JsonResponseHandler;

class Application {

    private static $CONFIG_DIRECTORY_PATH = '/configs';

    /** @var AppContainer */
    protected $container;

    /** @var static */
    private static $INSTANCE;

    /** @var string */
    private $basePath = '';

    /** @var ClosureInvoker */
    private $closureInvoker;

    /** @var FileStorage */
    private $fileStorage;

    private $isInitialized = false;

    /** @var MethodInvoker */
    private $methodInvoker;

    /** @var ResponseHandler[] */
    private $registeredResponseHandlers = [];

    private function __construct()
    {
        $this->container = new AppContainer();

        $parameterResolver = new ParameterResolver($this->container->getClassResolver());
        $this->closureInvoker = new ClosureInvoker($parameterResolver);
        $this->methodInvoker = new MethodInvoker($parameterResolver);
    }

    public function init()
    {
        if ($this->isInitialized)
        {
            return;
        }

        /** @var FileStorage $fileStorage */
        $this->fileStorage = new FileStorage();

        $this->loadConfiguration('base-config.php', 'config.php');
        $this->registerConnections();

        $this->loadConfiguration('base-registry.php', 'registry.php', 'registry');
        $this->loadConfiguration('base-routes.php', 'routes.php', 'routes');

        $this->loadRegistryData();
        $this->loadRouteData();

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
        $registeredCommand = $this->container->getCommandRegistry()->get($commandKey);

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
        $event = $this->container->getEventRegistry()->get($eventKey);

        if (!is_null($event))
        {
            $event->handle($data);
        }
    }

    /**
     * @param $class
     * @param array $namedParameters
     * @return mixed|object
     * @throws \Exception
     */
    public function getClass($class, $namedParameters = [])
    {
        return $this->container->resolveClass($class, $namedParameters);
    }

    /**
     * @return Connection
     */
    public function getConnection($key = 'default')
    {
        $connection = ConnectionRepository::get($key);

        return (!is_null($connection) ? $connection : new NullConnection());
    }

    /**
     * @return AppContainer
     */
    public function getContainer()
    {
        return $this->container;
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

    public function getRegisteredCommands()
    {
        return $this->container->getCommandRegistry()->getAll();
    }

    public function getRegisteredConfigs($key = null, $defaultValue = null)
    {
        if (is_null($key))
        {
            return $this->container->getConfigRegistry()->getAll();
        }

        $registeredConfig = $this->container->getConfigRegistry()->get($key);

        if (is_null($registeredConfig))
        {
            $registeredConfig = $defaultValue;
        }

        return $registeredConfig;
    }

    public function getRegisteredEvents()
    {
        return $this->container->getEventRegistry()->getAll();
    }

    public function getRegisteredRoutes($method = null, $path = null)
    {
        if (is_null($method))
        {
            return $this->getRouteRegistry()->getAll();
        }

        return $this->getRouteRegistry()->get($method, $path);
    }

    /**
     * @return RouteRegistry
     */
    public function getRouteRegistry()
    {
        return $this->container->getRouteRegistry();
    }

    public function getTemplatesPath()
    {
        return $this->getBasePath() . $this->getRegisteredConfigs('paths.templates');
    }

    public function handleRequest(Request $request)
    {
        $this->init();
        $this->registerSingleton(Request::class, $request);

        $this->registerDefaultResponseHandlers();

        /** @var ExceptionHandler $exceptionHandler */
        $exceptionHandler = $this->getClass(ExceptionHandler::class);
        $requestHandler = new RequestHandler($this->container, $this->getRouteRegistry(), $this->closureInvoker, $this->methodInvoker, $exceptionHandler);

        $requestHandler->setPreInvocationCallback(function($controller) use ($request) {
            if ($controller instanceof AbstractController)
            {
                $controller->setRequest($request);
                $controller->init();
            }
        });

        $response = $requestHandler->handle($request);
        $this->handleResponse($response, $exceptionHandler);
    }

    /**
     * @param $closure
     * @param array $namedParameters
     * @return object
     * @throws \Exception
     */
    public function invokeClosure($closure, $namedParameters = array())
    {
        return $this->closureInvoker->invoke($closure, $namedParameters);
    }

    /**
     * @param $class
     * @param $methodName
     * @param array $namedParameters
     * @return mixed
     * @throws \Exception
     */
    public function invokeMethod($class, $methodName, $namedParameters = array())
    {
        return $this->methodInvoker->invoke($class, $methodName, $namedParameters);
    }

    public function registerResponseHandler(ResponseHandler $responseHandler)
    {
        $this->registeredResponseHandlers[] = $responseHandler;
    }

    public function setBasePath($basePath)
    {
        $this->basePath = rtrim($basePath, '/');
    }

    private function getConnectionFromConfiguration($configs)
    {
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
    }

    private function getValue($key, array $data, $defaultValue = null)
    {
        return (array_key_exists($key, $data) ? $data[$key] : (!is_null($defaultValue) ? $defaultValue : null));
    }

    private function handleResponse(Response $response, ExceptionHandler $exceptionHandler = null)
    {
        foreach ($this->registeredResponseHandlers as $responseHandler)
        {
            if ($responseHandler->canHandle($response))
            {
                try {
                    $responseHandler->handle($response);
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

                break;
            }
        }
    }

    private function loadConfiguration($baseFileName, $clientFileName, $rootPrefix = null)
    {
        // load base application configurations
        $baseConfigPath = __DIR__ . '/../configs/' . $baseFileName;
        $this->registerConfigurationFile($baseConfigPath, $rootPrefix);

        // load client application configurations
        $clientConfigPath = $this->getConfigsPath() . '/' . $clientFileName;
        $this->registerConfigurationFile($clientConfigPath, $rootPrefix);
    }

    private function loadRegistryData()
    {
        $registryConfig = $this->getRegisteredConfigs('registry');

        if (is_null($registryConfig))
        {
            return;
        }

        foreach ($registryConfig as $key => $value)
        {
            switch ($key) {
                case 'classes':
                    foreach ($value as $name => $class)
                    {
                        $this->registerClass($name, $class);
                    }
                    break;
                case 'singletons':
                    foreach ($value as $name => $class)
                    {
                        $this->registerSingleton($name, $class);
                    }
                    break;
                case 'commands':
                    foreach ($value as $name => $command)
                    {
                        $this->registerCommand($name, $command);
                    }
                    break;
                case 'events':
                    foreach ($value as $name => $event)
                    {
                        $this->registerEvent($name, $event);
                    }
                    break;
                default:
                    break;
            }
        }
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
                $this->registerRoute($route);
            }
            else if ($route instanceof RouteGroup)
            {
                $this->registerRouteGroup($route);
            }
        }
    }

    /**
     * @param $key
     * @param Command|Closure $command
     */
    private function registerCommand($key, $command)
    {
        $this->container->getCommandRegistry()->register($key, $command);
    }

    /**
     * @param $filePath
     * @param null $rootPrefix
     */
    private function registerConfigurationFile($filePath, $rootPrefix = null)
    {
        if ($this->fileStorage->fileExists($filePath))
        {
            $configData = $this->fileStorage->require($filePath);

            if (!is_null($rootPrefix))
            {
                $configData = [$rootPrefix => $configData];
            }

            $this->container->getConfigRegistry()->register($configData);
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

        // support old connection configuration
        if (!array_key_exists('connections', $configs))
        {
            ConnectionRepository::register($this->getConnectionFromConfiguration($configs));
            return;
        }

        $connections = $configs['connections'];

        foreach ($connections as $key => $configData)
        {
            ConnectionRepository::register($this->getConnectionFromConfiguration($configData), $key);
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

    private function registerDefaultResponseHandlers()
    {
        $this->registerResponseHandler(new JsonResponseHandler());
        $this->registerResponseHandler(new XmlResponseHandler());
        $this->registerResponseHandler(new ModelResponseHandler());

        $templatesPath = $this->getTemplatesPath();
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
        
        $this->registerResponseHandler(new TwigResponseHandler($templatesPath, $twigConfigs));
        $this->registerResponseHandler(new ViewResponseHandler($templatesPath));
        $this->registerResponseHandler(new ArrayResponseHandler());
        $this->registerResponseHandler(new StringResponseHandler());
    }

    /**
     * @param $key
     * @param Event $event
     */
    private function registerEvent($key, Event $event)
    {
        $this->container->getEventRegistry()->register($key, $event);
    }

    /**
     * @param Route $route
     */
    private function registerRoute(Route $route)
    {
        $this->container->getRouteRegistry()->registerRoute($route);
    }

    /**
     * @param RouteGroup $routeGroup
     */
    private function registerRouteGroup(RouteGroup $routeGroup)
    {
        $this->container->getRouteRegistry()->registerRouteGroup($routeGroup);
    }

    /**
     * @param $name
     * @param $class
     */
    private function registerClass($name, $class)
    {
        $this->container->getClassRegistry()->register($name, $class);
    }

    /**
     * @param $name
     * @param $class
     */
    private function registerSingleton($name, $class)
    {
        $this->container->getClassRegistry()->register($name, $class, true);
    }

}