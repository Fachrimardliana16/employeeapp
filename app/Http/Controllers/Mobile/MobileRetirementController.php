<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\EmployeeRetirement;
use App\Models\MasterEmployeeRetirementType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MobileRetirementController extends Controller
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

        $requests = $employee
            ? EmployeeRetirement::where('employee_id', $employee->id)
                ->orderBy('created_at', 'desc')
                ->get()
            : collect();

        $retirementTypes = MasterEmployeeRetirementType::where('is_active', true)->get();

        return view('mobile.retirement.index', compact('employee', 'requests', 'retirementTypes'));
    }

    public function store(Request $request)
    {
        $employee = $this->getEmployee();
        if (!$employee) {
            return back()->with('error', 'Data pegawai tidak ditemukan.');
        }

        $validated = $request->validate([
            'master_employee_retirement_type_id' => 'required|exists:master_employee_retirement_types,id',
            'last_working_day' => 'required|date|after:today',
            'reason' => 'required|string|max:1000',
            'forwarding_address' => 'nullable|string|max:500',
            'forwarding_phone' => 'nullable|string|max:20',
        ], [
            'master_employee_retirement_type_id.required' => 'Jenis pengajuan wajib dipilih.',
            'last_working_day.required' => 'Hari terakhir bekerja wajib diisi.',
            'reason.required' => 'Alasan wajib diisi.',
        ]);

        EmployeeRetirement::create([
            'employee_id' => $employee->id,
            'master_employee_retirement_type_id' => $validated['master_employee_retirement_type_id'],
            'retirement_type' => MasterEmployeeRetirementType::find($validated['master_employee_retirement_type_id'])->name,
            'last_working_day' => $validated['last_working_day'],
            'reason' => $validated['reason'],
            'forwarding_address' => $validated['forwarding_address'],
            'forwarding_phone' => $validated['forwarding_phone'],
            'approval_status' => 'pending',
            'users_id' => Auth::id(),
        ]);

        return redirect()->route('mobile.retirement')
            ->with('success', 'Pengajuan pensiun/resign berhasil dikirim.');
    }
}
