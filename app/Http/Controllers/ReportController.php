<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\EmployeePromotion;
use App\Models\EmployeeMutation;
use App\Models\EmployeeRetirement;
use App\Models\EmployeeAppointment;
use App\Models\EmployeePeriodicSalaryIncrease;
use App\Models\Employee;
use Illuminate\Support\Carbon;

class ReportController extends Controller
{
    public function careerMovement(Request $request)
    {
        $type = $request->query('type');
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');
        $employeeId = $request->query('employee_id');

        $query = $this->getQueryForType($type);
        if (!$query) {
            abort(404, 'Report type not found');
        }

        // Apply Common Filters
        $dateColumn = $this->getDateColumnForType($type);
        
        $query->when($startDate, fn($q) => $q->whereDate($dateColumn, '>=', $startDate))
              ->when($endDate, fn($q) => $q->whereDate($dateColumn, '<=', $endDate))
              ->when($employeeId, fn($q) => $q->where('employee_id', $employeeId));

        $data = $query->with('employee')->orderBy($dateColumn, 'desc')->get();
        
        $title = $this->getTitleForType($type);
        $employeeName = $employeeId ? Employee::find($employeeId)?->name : 'Semua Pegawai';

        $pdf = Pdf::loadView('reports.career-movement', [
            'type' => $type,
            'title' => $title,
            'data' => $data,
            'startDate' => $startDate ? Carbon::parse($startDate)->format('d/m/Y') : '-',
            'endDate' => $endDate ? Carbon::parse($endDate)->format('d/m/Y') : '-',
            'employeeName' => $employeeName,
            'dateColumn' => $dateColumn
        ])->setPaper('a4', 'landscape');

        return $pdf->stream($type . '_report_' . now()->format('Ymd') . '.pdf');
    }

    protected function getQueryForType($type)
    {
        return match ($type) {
            'promotion' => EmployeePromotion::query(),
            'mutation' => EmployeeMutation::query(),
            'retirement' => EmployeeRetirement::query(),
            'appointment' => EmployeeAppointment::query(),
            'psi' => EmployeePeriodicSalaryIncrease::query(),
            'career_movement' => \App\Models\EmployeeCareerMovement::query(),
            default => null,
        };
    }

    protected function getDateColumnForType($type)
    {
        return match ($type) {
            'promotion' => 'promotion_date',
            'mutation' => 'mutation_date',
            'retirement' => 'retirement_date',
            'appointment' => 'appointment_date',
            'psi' => 'date_periodic_salary_increase',
            'career_movement' => 'movement_date',
            default => 'created_at',
        };
    }

    protected function getTitleForType($type)
    {
        return match ($type) {
            'promotion' => 'Laporan Kenaikan Golongan',
            'mutation' => 'Laporan Mutasi Pegawai',
            'retirement' => 'Laporan Pensiun Pegawai',
            'appointment' => 'Laporan Pengangkatan Pegawai',
            'psi' => 'Laporan Kenaikan Gaji Berkala (KGB)',
            'career_movement' => 'Laporan Promosi & Demosi Pegawai',
            default => 'Laporan Operasional Pegawai',
        };
    }

    public function careerSchedule(Request $request)
    {
        $year = $request->query('year', now()->addYear()->year);
        
        $employees = Employee::with(['grade', 'position', 'serviceGrade'])
            ->dueForCareerActionInYear($year)
            ->get();

        // 1. Group KGB by month
        $kgbData = $employees->filter(fn($e) => optional($e->next_kgb_date)->year == $year)
            ->groupBy(fn($e) => $e->next_kgb_date->format('m'))
            ->sortKeys();

        // 2. Group Promotion by month
        $promoData = $employees->filter(fn($e) => optional($e->next_promotion_date)->year == $year)
            ->groupBy(fn($e) => $e->next_promotion_date->format('m'))
            ->sortKeys();

        $pdf = Pdf::loadView('reports.career-schedule', [
            'year' => $year,
            'kgbData' => $kgbData,
            'promoData' => $promoData,
            'generated_at' => now()->translatedFormat('d F Y H:i'),
        ])->setPaper('a4', 'landscape');

        return $pdf->stream('Jadwal_KGB_Golongan_' . $year . '.pdf');
    }

    public function kgbSchedule(Request $request)
    {
        $year = $request->query('year', now()->year);
        
        $employees = Employee::with(['grade', 'position', 'serviceGrade'])
            ->dueForKgbInYear($year)
            ->get();

        $kgbData = $employees->groupBy(fn($e) => $e->next_kgb_date->format('m'))
            ->sortKeys();

        $pdf = Pdf::loadView('reports.kgb-schedule', [
            'year' => $year,
            'kgbData' => $kgbData,
            'generated_at' => now()->translatedFormat('d F Y H:i'),
        ])->setPaper('a4', 'landscape');

        return $pdf->stream('Jadwal_KGB_' . $year . '.pdf');
    }

    public function promotionSchedule(Request $request)
    {
        $year = $request->query('year', now()->year);
        
        $employees = Employee::with(['grade', 'position', 'serviceGrade'])
            ->dueForPromotionInYear($year)
            ->get();

        $promoData = $employees->groupBy(fn($e) => $e->next_promotion_date->format('m'))
            ->sortKeys();

        $pdf = Pdf::loadView('reports.promotion-schedule', [
            'year' => $year,
            'promoData' => $promoData,
            'generated_at' => now()->translatedFormat('d F Y H:i'),
        ])->setPaper('a4', 'landscape');

        return $pdf->stream('Jadwal_Kenaikan_Golongan_' . $year . '.pdf');
    }

    public function contractSchedule(Request $request)
    {
        $year = $request->query('year', now()->year);
        
        $agreements = \App\Models\EmployeeAgreement::with(['employee', 'department', 'masterAgreement'])
            ->dueToExpireInYear($year)
            ->get();

        $contractData = $agreements->groupBy(fn($e) => $e->agreement_date_end->format('m'))
            ->sortKeys();

        $pdf = Pdf::loadView('reports.contract-schedule', [
            'year' => $year,
            'contractData' => $contractData,
            'generated_at' => now()->translatedFormat('d F Y H:i'),
        ])->setPaper('a4', 'landscape');

        return $pdf->stream('Jadwal_Habis_Kontrak_' . $year . '.pdf');
    }
}
