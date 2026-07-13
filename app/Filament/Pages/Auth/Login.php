<?php

namespace App\Filament\Pages\Auth;

use App\Models\User;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Auth\Login as BaseLogin;

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
        /** @var User $user */
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

    protected function getCredentialsFromFormData(array $data): array
    {
        $loginInput = trim((string) ($data['email'] ?? ''));
        $password = (string) ($data['password'] ?? '');

        if ($loginInput === '') {
            return [
                'email' => '',
                'password' => $password,
                'is_active' => true,
            ];
        }

        if (filter_var($loginInput, FILTER_VALIDATE_EMAIL)) {
            return [
                'email' => strtolower($loginInput),
                'password' => $password,
                'is_active' => true,
            ];
        }

        // Support login by username OR email local-part (before @).
        $matchedUser = User::query()
            ->where('username', $loginInput)
            ->orWhere('email', 'like', $loginInput.'@%')
            ->first();

        if ($matchedUser) {
            return [
                'email' => $matchedUser->email,
                'password' => $password,
                'is_active' => true,
            ];
        }

        return [
            'username' => $loginInput,
            'password' => $password,
            'is_active' => true,
        ];
    }

    protected function getEmailFormComponent(): Component
    {
        return TextInput::make('email')
            ->label('EMAIL / USERNAME')
            ->placeholder('Masukkan email atau username anda')
            ->required()
            ->autocomplete()
            ->autofocus()
            ->extraInputAttributes(['tabindex' => 1])
            ->prefixIcon('heroicon-o-user')
            ->prefixIconColor('blue');
    }

    protected function getPasswordFormComponent(): Component
    {
        return parent::getPasswordFormComponent()
            ->label('PASSWORD')
            ->placeholder('Masukkan password anda')
            ->prefixIcon('heroicon-o-lock-closed')
            ->prefixIconColor('blue')
            ->revealable();
    }
}
