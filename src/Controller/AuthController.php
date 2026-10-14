<?php

declare(strict_types=1);

namespace EWnioski\Controller;

use EWnioski\Http\Request;
use EWnioski\Http\Response;
use EWnioski\Kernel\Container;
use EWnioski\Security\AuthContext;

final class AuthController
{
    public function __construct(
        private readonly Container $c,
        private readonly AuthContext $auth,
    ) {
    }

    public function showLogin(Request $request): Response
    {
        if ($this->auth->isLoggedIn()) {
            return Response::redirect('dashboard');
        }

        $content = $this->c->view()->render('auth/login', [
            'csrfField' => $this->c->csrf()->field(),
        ]);

        return Response::html($this->layout('Logowanie', $content, $request));
    }

    public function login(Request $request): Response
    {
        if (!$this->c->csrf()->isValid($request->input('_csrf'))) {
            return $this->loginError($request, 'Nieprawidłowy token formularza. Spróbuj ponownie.');
        }

        $user = $this->c->authenticator()->attempt(
            $request->trimmed('login'),
            (string) $request->input('password', ''),
        );

        if ($user === null) {
            // Jeden komunikat dla złego loginu i złego hasła (brak user enumeration),
            // hasło NIE jest logowane ani odsyłane.
            return $this->loginError($request, 'Nieprawidłowy numer ewidencyjny lub hasło.');
        }

        return Response::redirect('dashboard');
    }

    public function logout(Request $request): Response
    {
        $this->c->authenticator()->logout();

        return Response::redirect('login');
    }

    private function loginError(Request $request, string $message): Response
    {
        $content = $this->c->view()->render('auth/login', [
            'csrfField' => $this->c->csrf()->field(),
        ]);

        return Response::html($this->layout('Logowanie', $content, $request, $message, 'err'), 401);
    }

    private function layout(string $title, string $content, Request $request, ?string $flash = null, string $flashType = 'ok'): string
    {
        return $this->c->view()->render('layout', [
            'title' => $title,
            'content' => $content,
            'flash' => $flash,
            'flashType' => $flashType,
        ]);
    }
}
