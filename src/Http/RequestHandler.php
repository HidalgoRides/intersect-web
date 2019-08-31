<?php

namespace Intersect\Http;

use Intersect\Core\Container;
use Intersect\Core\Http\Request;
use Intersect\Core\MethodInvoker;
use Intersect\Core\ClosureInvoker;
use Intersect\Core\ParameterResolver;
use Intersect\Core\Http\Router\RouteResolver;
use Intersect\Http\ExceptionHandler;
use Intersect\Http\Response\Response;
use Intersect\Http\Response\StandardResponse;

class RequestHandler {

    /** @var ClosureInvoker $closureInvoker */
    private $closureInvoker;

    /** @var Container $container */
    private $container;

    /** @var ExceptionHandler */
    private $exceptionHandler;

    /** @var MethodInvoker $methodInvoker */
    private $methodInvoker;

    /** @var RouteResolver */
    private $routeResolver;

    private $preInvocationCallback;

    /**
     * RequestHandler constructor.
     */
    public function __construct(Container $container, ExceptionHandler $customExceptionHandler = null)
    {
        $parameterResolver = new ParameterResolver($container->getClassResolver());
        $this->closureInvoker = new ClosureInvoker($parameterResolver);
        $this->methodInvoker = new MethodInvoker($parameterResolver);
        
        $this->container = $container;
        $this->routeResolver = new RouteResolver($container->getRouteRegistry());

        $this->exceptionHandler = (!is_null($customExceptionHandler)) ? $customExceptionHandler : new DefaultExceptionHandler();
    }

    public function setPreInvocationCallback(\Closure $callback)
    {
        $this->preInvocationCallback = $callback;
    }

    /**
     * @param Request $request
     * @return Response
     * @throws \Exception
     */
    public function handle(Request $request)
    {
        $response = null;

        try {
            $routeAction = $this->routeResolver->resolve($request->getMethod(), $request->getBaseUri());

            if (is_null($routeAction))
            {
                throw new \Exception('Route not found: [uri: ' . $request->getBaseUri() . ']');
            }

            if ($routeAction->getIsCallable())
            {
                $response = $this->closureInvoker->invoke($routeAction->getMethod(), $routeAction->getNamedParameters());
            }
            else
            {
                $controllerClass = $routeAction->getController();

                if (is_null($controllerClass))
                {
                    throw new \Exception('Controller not found: [name: ' . $routeAction->getController() . ']');
                }

                $controller = $this->container->resolveClass($controllerClass);

                if (!method_exists($controller, $routeAction->getMethod()))
                {
                    throw new \Exception('Method not found: [name: ' . $routeAction->getController() . '#' . $routeAction->getMethod() . ']');
                }

                if (!is_null($this->preInvocationCallback))
                {
                    $callback = $this->preInvocationCallback;
                    $callback($controller);
                }

                $response = $this->methodInvoker->invoke($controller, $routeAction->getMethod(), $routeAction->getNamedParameters());
            }
        } catch (\Exception $e) {
            $response = $this->exceptionHandler->handle($e);
        }

        if (!$response instanceof Response)
        {
            $response = new StandardResponse($response);
        }

        return $response;
    }

}