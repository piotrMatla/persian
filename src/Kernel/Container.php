<?php

declare(strict_types=1);

namespace EWnioski\Kernel;

use EWnioski\Database\Database;
use EWnioski\Repository\HolidayRepository;
use EWnioski\Repository\LeaveBalanceRepository;
use EWnioski\Repository\LeaveRequestRepository;
use EWnioski\Repository\UserRepository;
use EWnioski\Security\Authenticator;
use EWnioski\Security\Csrf;
use EWnioski\Security\Session;
use EWnioski\Service\LeaveRequestService;
use EWnioski\View\TemplateRenderer;

/**
 * Bardzo lekki kontener usług (lazy singletony).
 *
 * Celowo bez magii – każda zależność ma jawną metodę fabrykującą,
 * co ułatwia czytanie i testowanie w porównaniu ze starym
 * `new Common; new HTML;` rozsianym po kontrolerach.
 */
final class Container
{
    /** @var array<string,object> */
    private array $instances = [];

    public function __construct(private readonly array $config)
    {
    }

    /**
     * @return array<string,mixed>
     */
    public function config(?string $key = null): mixed
    {
        if ($key === null) {
            return $this->config;
        }

        return $this->config[$key] ?? null;
    }

    public function database(): Database
    {
        return $this->instances[Database::class] ??= new Database($this->config['db']);
    }

    public function session(): Session
    {
        return $this->instances[Session::class] ??= new Session();
    }

    public function csrf(): Csrf
    {
        return $this->instances[Csrf::class] ??= new Csrf($this->session());
    }

    public function users(): UserRepository
    {
        return $this->instances[UserRepository::class] ??= new UserRepository($this->database());
    }

    public function holidays(): HolidayRepository
    {
        return $this->instances[HolidayRepository::class] ??= new HolidayRepository($this->database());
    }

    public function leaveRequests(): LeaveRequestRepository
    {
        return $this->instances[LeaveRequestRepository::class] ??= new LeaveRequestRepository($this->database());
    }

    public function leaveBalances(): LeaveBalanceRepository
    {
        return $this->instances[LeaveBalanceRepository::class] ??= new LeaveBalanceRepository($this->database());
    }

    public function authenticator(): Authenticator
    {
        return $this->instances[Authenticator::class] ??= new Authenticator($this->users(), $this->session());
    }

    public function leaveService(): LeaveRequestService
    {
        return $this->instances[LeaveRequestService::class] ??= new LeaveRequestService(
            $this->leaveRequests(),
            $this->leaveBalances(),
            $this->holidays(),
            $this->users(),
        );
    }

    public function view(): TemplateRenderer
    {
        return $this->instances[TemplateRenderer::class] ??= new TemplateRenderer(
            dirname(__DIR__, 2) . '/templates',
            $this->config['app'],
        );
    }
}
