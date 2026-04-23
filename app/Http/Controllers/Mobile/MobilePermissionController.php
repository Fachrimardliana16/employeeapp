<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\EmployeePermission;
use App\Models\MasterEmployeePermission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MobilePermissionController extends Controller
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
        $permissions = $employee
            ? EmployeePermission::with('permission')
                ->where('employee_id', $employee->id)
                ->orderBy('created_at', 'desc')
                ->paginate(15)
            : collect();

        $totalApproved = $employee
            ? EmployeePermission::where('employee_id', $employee->id)->where('approval_status', 'approved')->count()
            : 0;
        $totalPending = $employee
            ? EmployeePermission::where('employee_id', $employee->id)->where('approval_status', 'pending')->count()
            : 0;

        return view('mobile.permissions.index', compact('employee', 'permissions', 'totalApproved', 'totalPending'));
    }

    public function create()
    {
        $permissionTypes = MasterEmployeePermission::orderBy('name')->get();
        return view('mobile.permissions.create', compact('permissionTypes'));
    }

    public function store(Request $request)
    {
        $employee = $this->getEmployee();
        if (!$employee) {
            return back()->with('error', 'Data pegawai tidak ditemukan.');
        }

        $validated = $request->validate([
            'permission_id'       => 'required|exists:master_employee_permissions,id',
            'start_permission_date' => 'required|date',
            'end_permission_date'   => 'required|date|after_or_equal:start_permission_date',
            'permission_desc'       => 'required|string|max:1000',
            'scan_doc'              => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ], [
            'permission_id.required'       => 'Jenis izin wajib dipilih.',
            'start_permission_date.required' => 'Tanggal mulai wajib diisi.',
            'end_permission_date.required'   => 'Tanggal selesai wajib diisi.',
            'end_permission_date.after_or_equal' => 'Tanggal selesai tidak boleh sebelum tanggal mulai.',
            'permission_desc.required'       => 'Alasan/keterangan wajib diisi.',
        ]);

        $scanDocPath = null;
        if ($request->hasFile('scan_doc')) {
            $scanDocPath = $request->file('scan_doc')->store('permissions', 'public');
        }

        EmployeePermission::create([
            'employee_id'           => $employee->id,
            'permission_id'         => $validated['permission_id'],
            'start_permission_date' => $validated['start_permission_date'],
            'end_permission_date'   => $validated['end_permission_date'],
            'permission_desc'       => $validated['permission_desc'],
            'scan_doc'              => $scanDocPath,
            'approval_status'       => 'pending',
            'users_id'              => Auth::id(),
        ]);

        return redirect()->route('mobile.permissions')
            ->with('success', 'Pengajuan izin/cuti berhasil dikirim! Menunggu persetujuan HRD.');
    }

    public function show($id)
    {
        $employee = $this->getEmployee();
        $permission = EmployeePermission::with(['permission', 'approver'])
            ->where('employee_id', $employee?->id)
            ->findOrFail($id);

        return view('mobile.permissions.show', compact('permission'));
    }
}
