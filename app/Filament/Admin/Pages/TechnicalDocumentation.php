<?php

namespace App\Filament\Admin\Pages;

use Filament\Pages\Page;

class TechnicalDocumentation extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-code-bracket';
    protected static ?string $navigationLabel = 'Technical Documentation';
    protected static ?string $navigationGroup = 'Developer';
    protected static ?int $navigationSort = 999;
    protected static string $view = 'filament.admin.pages.technical-documentation';

    public function getTitle(): string
    {
        return 'Technical Documentation - Developer Guide';
    }
}
