<?php

declare(strict_types=1);

namespace EWnioski\Routing;

use EWnioski\Domain\Enum\Role;
use EWnioski\Http\Request;
use EWnioski\Http\Response;
use EWnioski\Security\AuthContext;

final class Router
{
    /** @var list<Route> */
    private array $routes = [];

    /**
     * @param list<Role>|null $roles
     */
    public function add(string $method, string $path, callable $handler, ?array $roles = []): void
    {
        $this->routes[] = new Route(strtoupper($method), $path, $handler, $roles);
    }

    public function dispatch(Request $request, AuthContext $auth): Response
    {
        foreach ($this->routes as $route) {
            if ($route->method !== $request->method() || $route->path !== $request->path()) {
                continue;
            }

            // Autoryzacja egzekwowana centralnie, przy każdej trasie.
            if ($route->roles !== null) {
                if (!$auth->isLoggedIn()) {
                    return Response::redirect('login');
                }
                if ($route->roles !== [] && !$auth->hasAnyRole($route->roles)) {
                    return Response::html('<h1>403 – Brak uprawnień</h1>', 403);
                }
            }

            return ($route->handler)($request);
        }

        return Response::html('<h1>404 – Nie znaleziono</h1>', 404);
    }
}
