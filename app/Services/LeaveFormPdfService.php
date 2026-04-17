<?php

namespace App\Services;

use App\Models\EmployeePermission;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class LeaveFormPdfService
{
    /**
     * Generate Leave Form PDF.
     */
    public function generateLeaveForm(EmployeePermission $permission)
    {
        $employee = $permission->employee;
        
        // Prepare data for the view
        $data = [
            'permission' => $permission,
            'employee' => $employee,
            'signatory' => $permission->approver ?? null,
            'date_generated' => Carbon::now()->translatedFormat('d F Y'),
        ];

        // Load the view and set paper size
        $pdf = Pdf::loadView('pdf.leave_form', $data)
            ->setPaper('a4', 'portrait');

        return $pdf;
    }
}
