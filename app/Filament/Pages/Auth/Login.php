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

        if ($user->hasPermissionTo('access_admin_panel')) {
            return '/admin';
        }

        if ($user->hasPermissionTo('access_employee_panel')) {
            return '/employee';
        }

        if ($user->hasPermissionTo('access_user_panel')) {
            return '/user';
        }

        return parent::getRedirectUrl();
    }

    protected function getCredentialsFromFormData(array $data): array
    {
        $loginField = filter_var($data['email'], FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        return [
            $loginField => $data['email'],
            'password' => $data['password'],
            'is_active' => true,
        ];
    }

    protected function getEmailFormComponent(): \Filament\Forms\Components\Component
    {
        return \Filament\Forms\Components\TextInput::make('email')
            ->label('EMAIL / USERNAME')
            ->placeholder('Masukkan email atau username anda')
            ->required()
            ->autocomplete()
            ->autofocus()
            ->extraInputAttributes(['tabindex' => 1])
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
