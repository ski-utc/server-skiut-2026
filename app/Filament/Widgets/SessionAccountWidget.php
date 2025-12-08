<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class SessionAccountWidget extends Widget
{
    protected static string $view = 'filament.widgets.session-account-widget';

    public function getUserData(): array
    {
        $email = session('email');

        return [
            'email' => $email,
            'name' => ucfirst(explode('.', $email)[0]).' '.ucfirst(explode('@', explode('.', $email)[1])[0]) ?? 'Utilisateur',
        ];
    }

    public function isAdmin(): bool
    {
        return session('admin') === true;
    }
}
