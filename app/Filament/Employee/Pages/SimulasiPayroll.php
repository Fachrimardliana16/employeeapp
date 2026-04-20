<?php

namespace App\Filament\Employee\Pages;

use App\Models\Employee;
use App\Services\PayrollService;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Carbon\Carbon;
use Filament\Actions\Action;

class SimulasiPayroll extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-calculator';

    protected static ?string $navigationGroup = 'Kompensasi & Tunjangan';

    protected static ?string $navigationLabel = 'Simulasi Payroll';

    protected static ?int $navigationSort = 402;

    protected static string $view = 'filament.employee.pages.simulasi-payroll';

    public ?array $data = [];
    public ?array $payrollResult = null;
    public ?Employee $selectedEmployee = null;

    public function mount(): void
    {
        $this->form->fill([
            'payroll_period' => now()->format('Y-m-d'),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('employee_id')
                    ->label('Pilih Pegawai')
                    ->options(Employee::pluck('name', 'id'))
                    ->required()
                    ->searchable()
                    ->live()
                    ->afterStateUpdated(fn () => $this->calculate()),
                DatePicker::make('payroll_period')
                    ->label('Periode Gaji')
                    ->required()
                    ->native(false)
                    ->displayFormat('F Y')
                    ->live()
                    ->afterStateUpdated(fn () => $this->calculate()),
            ])
            ->statePath('data')
            ->columns(2);
    }

    public function calculate(): void
    {
        $employeeId = $this->data['employee_id'] ?? null;
        $period = $this->data['payroll_period'] ?? null;

        if (!$employeeId || !$period) {
            $this->payrollResult = null;
            $this->selectedEmployee = null;
            return;
        }

        $this->selectedEmployee = Employee::with(['position', 'grade'])->find($employeeId);
        
        if (!$this->selectedEmployee) {
            $this->payrollResult = null;
            return;
        }

        $payrollService = app(PayrollService::class);
        $this->payrollResult = $payrollService->getPayrollData(
            $this->selectedEmployee,
            Carbon::parse($period)
        );
    }

    public function getTitle(): string
    {
        return 'Simulasi & Pratinjau Payroll';
    }
}
