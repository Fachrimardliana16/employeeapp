<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\EmployeeDailyReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MobileDailyReportController extends Controller
{
    private function getEmployee()
    {
        $user = Auth::user();
        return Employee::where('users_id', $user->id)
            ->orWhere('email', $user->email)
            ->orWhere('office_email', $user->email)
            ->first();
    }

    public function index()
    {
        $employee = $this->getEmployee();
        $reports = $employee
            ? EmployeeDailyReport::where('employee_id', $employee->id)
                ->orderBy('daily_report_date', 'desc')
                ->paginate(15)
            : collect();

        $todayReport = $employee
            ? EmployeeDailyReport::where('employee_id', $employee->id)
                ->whereDate('daily_report_date', today())
                ->first()
            : null;

        return view('mobile.daily-reports.index', compact('employee', 'reports', 'todayReport'));
    }

    public function store(Request $request)
    {
        $employee = $this->getEmployee();
        if (!$employee) {
            return back()->with('error', 'Data pegawai tidak ditemukan.');
        }

        $validated = $request->validate([
            'daily_report_date' => 'required|date',
            'work_description'  => 'required|string|max:2000',
            'work_status'       => 'required|in:completed,in_progress,pending',
            'desc'              => 'nullable|string|max:1000',
        ], [
            'daily_report_date.required' => 'Tanggal laporan wajib diisi.',
            'work_description.required'  => 'Isi laporan wajib diisi.',
            'work_status.required'       => 'Status pekerjaan wajib dipilih.',
        ]);

        EmployeeDailyReport::create([
            'employee_id'       => $employee->id,
            'daily_report_date' => $validated['daily_report_date'],
            'work_description'  => $validated['work_description'],
            'work_status'       => $validated['work_status'],
            'desc'              => $validated['desc'],
            'users_id'          => Auth::id(),
        ]);

        return redirect()->route('mobile.daily-reports')
            ->with('success', 'Laporan harian berhasil disimpan.');
    }
}
