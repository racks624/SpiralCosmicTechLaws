<?php
namespace App\Core;

class Router
{
    protected array $routes = [];
    protected array $routeNames = [];
    protected array $middlewares = [];

    public function get($uri, $handler, $name = null)
    {
        $this->addRoute('GET', $uri, $handler, $name);
    }

    public function post($uri, $handler, $name = null)
    {
        $this->addRoute('POST', $uri, $handler, $name);
    }

    public function put($uri, $handler, $name = null)
    {
        $this->addRoute('PUT', $uri, $handler, $name);
    }

    public function delete($uri, $handler, $name = null)
    {
        $this->addRoute('DELETE', $uri, $handler, $name);
    }

    private function addRoute($method, $uri, $handler, $name)
    {
        $uri = $this->normalizeUri($uri);
        $this->routes[] = [
            'method' => $method,
            'uri' => $uri,
            'handler' => $handler,
            'name' => $name
        ];
        if ($name) {
            $this->routeNames[$name] = $uri;
        }
    }

    public function middleware($middleware, $callback)
    {
        // Register middleware for a group (simplified)
        $this->middlewares[] = ['middleware' => $middleware, 'callback' => $callback];
    }

    private function normalizeUri($uri)
    {
        return '/' . trim($uri, '/');
    }

    public function dispatch(Request $request)
    {
        $uri = $request->getUri();
        $method = $request->getMethod();

        foreach ($this->routes as $route) {
            if ($route['uri'] === $uri && $route['method'] === $method) {
                // Apply global middlewares
                foreach ($this->middlewares as $mw) {
                    $result = call_user_func($mw['middleware'], $request);
                    if ($result === false) {
                        http_response_code(403);
                        echo "Forbidden";
                        return;
                    }
                }

                $handler = $route['handler'];
                if (is_string($handler) && strpos($handler, '@') !== false) {
                    [$controller, $action] = explode('@', $handler);
                    $controllerClass = "App\\Controllers\\{$controller}";
                    if (class_exists($controllerClass)) {
                        $controllerInstance = new $controllerClass();
                        if (method_exists($controllerInstance, $action)) {
                            return $controllerInstance->$action($request);
                        }
                    }
                    throw new \Exception("Controller or method not found: {$controller}@{$action}");
                } elseif (is_callable($handler)) {
                    return call_user_func($handler, $request);
                }
                throw new \Exception("Invalid route handler");
            }
        }
        http_response_code(404);
        echo json_encode(['error' => 'Route not found']);
    }

    public function route($name, $params = [])
    {
        if (!isset($this->routeNames[$name])) {
            return '#';
        }
        $uri = $this->routeNames[$name];
        foreach ($params as $key => $value) {
            $uri = str_replace("{{$key}}", $value, $uri);
        }
        return $uri;
    }
}
