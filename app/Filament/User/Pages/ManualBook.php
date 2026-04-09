<?php

namespace App\Filament\User\Pages;

use Filament\Pages\Page;

class ManualBook extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-book-open';
    protected static ?string $navigationLabel = 'Buku Panduan';
    protected static ?string $navigationGroup = 'Bantuan';
    protected static ?int $navigationSort = 999;
    protected static string $view = 'filament.user.pages.manual-book';

    public function getTitle(): string
    {
        return 'Buku Panduan - Panduan Penggunaan';
    }
}
