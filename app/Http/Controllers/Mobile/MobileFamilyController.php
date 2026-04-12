<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\EmployeeFamily;
use App\Models\MasterEmployeeFamily;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MobileFamilyController extends Controller
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

        $families = $employee
            ? EmployeeFamily::with('masterFamily')
                ->where('employees_id', $employee->id) // Note: employees_id based on model
                ->get()
            : collect();

        $masterFamilies = MasterEmployeeFamily::where('is_active', true)->get();

        return view('mobile.family.index', compact('employee', 'families', 'masterFamilies'));
    }

    public function store(Request $request)
    {
        $employee = $this->getEmployee();
        if (!$employee) {
            return back()->with('error', 'Data pegawai tidak ditemukan.');
        }

        $validated = $request->validate([
            'master_employee_families_id' => 'required|exists:master_employee_families,id',
            'family_name' => 'required|string|max:255',
            'family_gender' => 'required|in:L,P',
            'family_id_number' => 'nullable|string|max:20',
            'family_place_birth' => 'nullable|string|max:100',
            'family_date_birth' => 'nullable|date',
        ], [
            'family_name.required' => 'Nama anggota keluarga wajib diisi.',
            'master_employee_families_id.required' => 'Hubungan keluarga wajib dipilih.',
        ]);

        EmployeeFamily::create([
            'employees_id' => $employee->id,
            'master_employee_families_id' => $validated['master_employee_families_id'],
            'family_name' => $validated['family_name'],
            'family_gender' => $validated['family_gender'],
            'family_id_number' => $validated['family_id_number'],
            'family_place_birth' => $validated['family_place_birth'],
            'family_date_birth' => $validated['family_date_birth'],
            'users_id' => Auth::id(),
        ]);

        return redirect()->route('mobile.family')
            ->with('success', 'Data keluarga berhasil ditambahkan.');
    }
}
