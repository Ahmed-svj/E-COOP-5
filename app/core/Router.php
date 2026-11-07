<?php
class Router
{
    private array $routes = [];
    private string $prefix = '';
    private array $groupMiddleware = [];

    public function get(string $path, string $handler, array $middleware = []): void
    {
        $this->add('GET', $path, $handler, $middleware);
    }

    public function post(string $path, string $handler, array $middleware = []): void
    {
        $this->add('POST', $path, $handler, $middleware);
    }

    public function group(string $prefix, array $middleware, callable $routes): void
    {
        [$oldPrefix, $oldMw] = [$this->prefix, $this->groupMiddleware];
        $this->prefix .= $prefix;
        $this->groupMiddleware = array_merge($this->groupMiddleware, $middleware);
        $routes($this);
        [$this->prefix, $this->groupMiddleware] = [$oldPrefix, $oldMw];
    }

    private function add(string $method, string $path, string $handler, array $middleware): void
    {
        $full = rtrim($this->prefix . $path, '/') ?: '/';
        $regex = preg_replace(
            ['#\{id\}#', '#\{[a-z]+\}#'],
            ['(\d+)', '([A-Za-z0-9_-]+)'],
            $full
        );
        $this->routes[] = [
            'method'     => $method,
            'regex'      => '#^' . $regex . '$#',
            'handler'    => $handler,
            'middleware' => array_merge($this->groupMiddleware, $middleware),
        ];
    }

    public function dispatch(string $method, string $uri): void
    {
        $uri = rtrim($uri, '/') ?: '/';
        $pathMatched = false;

        foreach ($this->routes as $route) {
            if (!preg_match($route['regex'], $uri, $m)) {
                continue;
            }
            $pathMatched = true;
            if ($route['method'] !== $method) {
                continue;
            }
            array_shift($m);

            if ($method === 'POST') {
                CsrfMiddleware::handle();
            }
            foreach ($route['middleware'] as $mw) {
                [$name, $arg] = array_pad(explode(':', $mw, 2), 2, null);
                match ($name) {
                    'auth'  => AuthMiddleware::handle(),
                    'guest' => AuthMiddleware::guest(),
                    'role'  => RoleMiddleware::handle(explode(',', (string) $arg)),
                };
            }

            [$class, $action] = explode('@', $route['handler']);
            $class = 'Controllers\\' . $class;
            (new $class())->$action(...array_map(fn ($v) => ctype_digit($v) ? (int) $v : $v, $m));
            return;
        }

        if ($pathMatched) {
            http_response_code(405);
        }
        abort($pathMatched ? 405 : 404);
    }
}
