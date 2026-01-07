<?php

namespace App\Filament\User\Pages;

use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use App\Models\Employee;

class DataKepegawaian extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-user-circle';
    protected static string $view = 'filament.user.pages.data-kepegawaian';
    protected static ?string $navigationLabel = 'Data Kepegawaian';
    protected static ?string $navigationGroup = 'Data Saya';
    protected static ?int $navigationSort = 1;
    
    public ?Employee $employee = null;
    
    public function mount(): void
    {
        $user = Auth::user();
        $this->employee = Employee::with([
            'families',
            'agreements',
            'attendanceRecords',
            'mutations',
            'promotions',
            'salaries',
            'trainings',
            'assignmentLetters',
            'businessTravelLetters',
            'dailyReports',
        ])
        ->where('email', $user->email)
        ->first();
    }
}
