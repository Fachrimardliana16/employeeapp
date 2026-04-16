<?php

namespace App\Filament\Pages\Auth;

use Filament\Pages\Auth\Login as BaseLogin;
use Filament\Models\Contracts\FilamentUser;

class Login extends BaseLogin
{
    protected static string $view = 'filament.pages.auth.login';

    protected static string $layout = 'layouts.auth';

    public function getHeading(): string
    {
        return 'Masuk ke Sistem';
    }

    public function mount(): void
    {
        parent::mount();
    }

    protected function getRedirectUrl(): string
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        if ($user->hasRole('superadmin')) {
            return '/admin';
        }

        if ($user->hasRole('admin')) {
            return '/employee';
        }

        if ($user->hasRole('user')) {
            return '/user';
        }

        return parent::getRedirectUrl();
    }

    protected function getEmailFormComponent(): \Filament\Forms\Components\Component
    {
        return parent::getEmailFormComponent()
            ->label('USERNAME')
            ->placeholder('Masukkan username anda')
            ->prefixIcon('heroicon-o-user')
            ->prefixIconColor('blue');
    }

    protected function getPasswordFormComponent(): \Filament\Forms\Components\Component
    {
        return parent::getPasswordFormComponent()
            ->label('PASSWORD')
            ->placeholder('Masukkan password anda')
            ->prefixIcon('heroicon-o-lock-closed')
            ->prefixIconColor('blue')
            ->revealable();
    }
}
