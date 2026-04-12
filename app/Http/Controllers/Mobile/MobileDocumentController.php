<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\EmployeeDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class MobileDocumentController extends Controller
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

        $documents = $employee
            ? EmployeeDocument::where('employee_id', $employee->id)
                ->orderBy('created_at', 'desc')
                ->get()
            : collect();

        $documentTypes = EmployeeDocument::getDocumentTypeOptions();

        return view('mobile.documents.index', compact('employee', 'documents', 'documentTypes'));
    }

    public function store(Request $request)
    {
        $employee = $this->getEmployee();
        if (!$employee) {
            return back()->with('error', 'Data pegawai tidak ditemukan.');
        }

        $validated = $request->validate([
            'document_type' => 'required|string',
            'document_name' => 'required|string|max:255',
            'document_number' => 'nullable|string|max:100',
            'file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'notes' => 'nullable|string|max:500',
        ], [
            'document_type.required' => 'Tipe dokumen wajib dipilih.',
            'document_name.required' => 'Nama dokumen wajib diisi.',
            'file.required' => 'File dokumen wajib diunggah.',
            'file.max' => 'Ukuran file maksimal 5MB.',
        ]);

        if ($request->hasFile('file')) {
            $path = $request->file('file')->store('employee-documents', 'public');

            EmployeeDocument::create([
                'employee_id' => $employee->id,
                'document_type' => $validated['document_type'],
                'document_name' => $validated['document_name'],
                'document_number' => $validated['document_number'],
                'file_path' => $path,
                'notes' => $validated['notes'],
                'uploaded_by' => Auth::id(),
                'users_id' => Auth::id(),
            ]);

            return redirect()->route('mobile.documents')
                ->with('success', 'Dokumen berhasil diunggah.');
        }

        return back()->with('error', 'Gagal mengunggah file.');
    }
}
