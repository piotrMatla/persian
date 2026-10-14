<?php

declare(strict_types=1);

namespace EWnioski\Controller;

use EWnioski\Domain\Enum\LeaveType;
use EWnioski\Domain\Leave\LeaveRequestDraft;
use EWnioski\Http\Request;
use EWnioski\Http\Response;
use EWnioski\Kernel\Container;
use EWnioski\Security\AuthContext;
use EWnioski\Service\ValidationException;

final class LeaveController
{
    public function __construct(
        private readonly Container $c,
        private readonly AuthContext $auth,
    ) {
    }

    public function form(Request $request): Response
    {
        $user = $this->auth->userOrFail();

        $types = array_map(
            static fn (LeaveType $t): array => ['value' => $t->value, 'label' => $t->label()],
            LeaveType::cases(),
        );

        $content = $this->c->view()->render('leave/form', [
            'csrfField' => $this->c->csrf()->field(),
            'types' => $types,
            'employeeNumber' => $user->employeeNumber,
        ]);

        return Response::html($this->layout('Nowy wniosek', $content, $this->flash()));
    }

    public function submit(Request $request): Response
    {
        if (!$this->c->csrf()->isValid($request->input('_csrf'))) {
            return $this->redirectWithFlash('leave', 'Nieprawidłowy token formularza.', 'err');
        }

        $user = $this->auth->userOrFail();

        try {
            $draft = new LeaveRequestDraft(
                targetEmployeeNumber: $request->trimmed('target', $user->employeeNumber) ?: $user->employeeNumber,
                type: LeaveType::from($request->int('type', LeaveType::Annual->value)),
                start: $this->date($request->trimmed('start')),
                end: $this->date($request->trimmed('end')),
            );

            $result = $this->c->leaveService()->submit($user, $draft);
        } catch (ValidationException $e) {
            return $this->redirectWithFlash('leave', $e->getMessage(), 'err');
        } catch (\ValueError|\Exception $e) {
            return $this->redirectWithFlash('leave', 'Nieprawidłowe dane wniosku.', 'err');
        }

        return $this->redirectWithFlash(
            'leave',
            sprintf('Wniosek #%d zapisany (status: %s, dni: %s).',
                $result->requestId,
                $result->status->label(),
                rtrim(rtrim(number_format($result->workingDays, 2, '.', ''), '0'), '.'),
            ),
            'ok',
        );
    }

    private function date(string $value): \DateTimeImmutable
    {
        $d = \DateTimeImmutable::createFromFormat('Y-m-d', $value);
        if ($d === false) {
            throw new ValidationException('Nieprawidłowy format daty.');
        }

        return $d->setTime(0, 0);
    }

    private function redirectWithFlash(string $to, string $message, string $type): Response
    {
        $this->c->session()->set('_flash', $message);
        $this->c->session()->set('_flash_type', $type);

        return Response::redirect($to);
    }

    /**
     * @return array{0:?string,1:string}
     */
    private function flash(): array
    {
        $msg = $this->c->session()->get('_flash');
        $type = $this->c->session()->get('_flash_type', 'ok');
        $this->c->session()->remove('_flash');
        $this->c->session()->remove('_flash_type');

        return [is_string($msg) ? $msg : null, is_string($type) ? $type : 'ok'];
    }

    /**
     * @param array{0:?string,1:string} $flash
     */
    private function layout(string $title, string $content, array $flash): string
    {
        return $this->c->view()->render('layout', [
            'title' => $title,
            'content' => $content,
            'flash' => $flash[0],
            'flashType' => $flash[1],
        ]);
    }
}
