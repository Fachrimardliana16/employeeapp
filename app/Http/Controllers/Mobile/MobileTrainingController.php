<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\EmployeeTraining;
use Illuminate\Support\Facades\Auth;

class MobileTrainingController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $employee = Employee::where('users_id', $user->id)
            ->orWhere('email', $user->email)
            ->first();

        $trainings = $employee
            ? EmployeeTraining::where('employee_id', $employee->id)
                ->orderBy('training_date', 'desc')
                ->get()
            : collect();

        return view('mobile.training.index', compact('employee', 'trainings'));
    }
}
