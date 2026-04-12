<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\EmployeeDocument;
use Illuminate\Support\Facades\Auth;

class MobileDocumentController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $employee = Employee::where('users_id', $user->id)
            ->orWhere('email', $user->email)
            ->first();

        $documents = $employee
            ? EmployeeDocument::where('employee_id', $employee->id)
                ->orderBy('created_at', 'desc')
                ->get()
            : collect();

        return view('mobile.documents.index', compact('employee', 'documents'));
    }
}
