<?php

declare(strict_types=1);

namespace EWnioski\Controller;

use EWnioski\Domain\Navigation\ModuleCatalog;
use EWnioski\Http\Request;
use EWnioski\Http\Response;
use EWnioski\Kernel\Container;
use EWnioski\Security\AuthContext;

/**
 * Pulpit (ekran główny). Lista kafelków jest filtrowana po roli
 * użytkownika PO STRONIE SERWERA – przeglądarka nie dostaje nawet
 * znacznika modułu, do którego użytkownik nie ma prawa.
 */
final class DashboardController
{
    public function __construct(
        private readonly Container $c,
        private readonly AuthContext $auth,
    ) {
    }

    public function index(Request $request): Response
    {
        $user = $this->auth->userOrFail();
        $catalog = new ModuleCatalog();

        $html = $this->c->view()->render('dashboard', [
            'user' => $user,
            'modules' => $catalog->visibleFor($user),
            'hasAdmin' => $catalog->hasAdminModules($user),
            // Profile/zakłady – docelowo z bazy (tabela profil); na razie statycznie.
            'profiles' => ['PKC — Zakład główny', 'Zakład 2', 'Zakład 3'],
        ]);

        return Response::html($html);
    }
}
