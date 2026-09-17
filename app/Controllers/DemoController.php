<?php

declare(strict_types=1);

namespace App\Controllers;

// Служебные страницы демо: уведомления и возврат к исходным данным.

use App\Services\EmployeeDirectory;
use App\Services\Notifications;
use App\Support\DemoData;
use App\Support\Response;
use App\Support\View;

final class DemoController
{
    public function __construct(
        private DemoData $state,
        private EmployeeDirectory $directory,
        private Notifications $notifications,
    ) {
    }

    public function notifications(): void
    {
        View::render('pages/notifications', [
            'pageTitle' => t('notifications.title'),
            'active' => 'notifications',
            'notifications' => $this->notifications->all(),
            'locale' => current_locale(),
            'csrf' => csrf_token(),
            'directory' => $this->directory,
            'config' => config(),
        ]);
    }

    public function reset(): never
    {
        $this->state->reset();
        Response::json(['ok' => true]);
    }
}
