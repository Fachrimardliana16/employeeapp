<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\EmployeeTraining;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MobileTrainingController extends Controller
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

        $trainings = $employee
            ? EmployeeTraining::where('employee_id', $employee->id)
                ->orderBy('training_date', 'desc')
                ->get()
            : collect();

        return view('mobile.training.index', compact('employee', 'trainings'));
    }

    public function store(Request $request)
    {
        $employee = $this->getEmployee();
        if (!$employee) {
            return back()->with('error', 'Data pegawai tidak ditemukan.');
        }

        $validated = $request->validate([
            'training_date' => 'required|date',
            'training_title' => 'required|string|max:255',
            'training_location' => 'nullable|string|max:255',
            'organizer' => 'nullable|string|max:255',
            'certificate' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ], [
            'training_date.required' => 'Tanggal pelatihan wajib diisi.',
            'training_title.required' => 'Judul/Nama pelatihan wajib diisi.',
        ]);

        $docsPath = null;
        if ($request->hasFile('certificate')) {
            $docsPath = $request->file('certificate')->store('employee-training-docs', 'public');
        }

        EmployeeTraining::create([
            'employee_id' => $employee->id,
            'training_date' => $validated['training_date'],
            'training_title' => $validated['training_title'],
            'training_location' => $validated['training_location'],
            'organizer' => $validated['organizer'],
            'docs_training' => $docsPath, // Using docs_training field based on model
            'users_id' => Auth::id(),
        ]);

        return redirect()->route('mobile.training')
            ->with('success', 'Riwayat pelatihan berhasil ditambahkan.');
    }
}
