<?php

declare(strict_types=1);

use EWnioski\Controller\AuthController;
use EWnioski\Controller\DashboardController;
use EWnioski\Controller\LeaveController;
use EWnioski\Domain\Enum\Role;
use EWnioski\Http\Request;
use EWnioski\Http\Response;
use EWnioski\Kernel\Container;
use EWnioski\Routing\Router;

/** @var Container $container */
$container = require dirname(__DIR__) . '/bootstrap.php';

$container->session()->start();

$request = Request::fromGlobals();
$auth = $container->authenticator()->currentContext();

$authController = new AuthController($container, $auth);
$leaveController = new LeaveController($container, $auth);
$dashboardController = new DashboardController($container, $auth);

$router = new Router();

// Trasy publiczne (roles = null).
$router->add('GET', '/login', $authController->showLogin(...), null);
$router->add('POST', '/login', $authController->login(...), null);

// Trasy wymagające zalogowania (roles = [] => dowolna rola).
$router->add('GET', '/logout', $authController->logout(...), []);
$router->add('GET', '/dashboard', $dashboardController->index(...), []);
$router->add('GET', '/leave', $leaveController->form(...), []);
$router->add('POST', '/leave/submit', $leaveController->submit(...), []);

// Strona główna -> logowanie lub pulpit.
$router->add('GET', '/', static function () use ($auth): Response {
    return Response::redirect($auth->isLoggedIn() ? 'dashboard' : 'login');
}, null);

try {
    $response = $router->dispatch($request, $auth);
} catch (\Throwable $e) {
    // Błędy nie wyciekają szczegółami na ekran (stara wersja pokazywała SQL).
    $debug = (bool) ($container->config('app')['debug'] ?? false);
    error_log('[eWnioski] ' . $e);
    $body = $debug
        ? '<h1>500</h1><pre>' . htmlspecialchars((string) $e, ENT_QUOTES) . '</pre>'
        : '<h1>500 – Wystąpił błąd</h1><p>Problem został zapisany. Skontaktuj się z działem IT.</p>';
    $response = Response::html($body, 500);
}

$response->send();
