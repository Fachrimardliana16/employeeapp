<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\EmployeeFamily;
use Illuminate\Support\Facades\Auth;

class MobileFamilyController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $employee = Employee::where('users_id', $user->id)
            ->orWhere('email', $user->email)
            ->first();

        $families = $employee
            ? EmployeeFamily::with('masterFamily')
                ->where('employee_id', $employee->id)
                ->get()
            : collect();

        return view('mobile.family.index', compact('employee', 'families'));
    }
}
