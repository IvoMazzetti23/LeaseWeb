<?php

namespace App;

use App\Http\Request;
use App\Http\Response;

class Router
{
    private array $routes = [];

    public function get(string $path, array $handler): void
    {
        $this->routes['GET'][$path] = $handler;
    }

    public function dispatch(Request $request): Response
    {
        $method = $request->method();
        $uri = $request->uri();

        if (!isset($this->routes[$method][$uri])) {
            return Response::json(['error' => 'Not Found'], 404);
        }

        [$class, $methodName] = $this->routes[$method][$uri];
        $controller = new $class();

        return $controller->$methodName($request);
    }
}
