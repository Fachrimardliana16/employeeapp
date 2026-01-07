<?php

namespace App\Models;

use App\Traits\HasUserTracking;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeRetirement extends Model
{
    use HasUserTracking;

    protected $fillable = [
        'employee_id',
        'retirement_type',
        'retirement_date',
        'last_working_day',
        'reason',
        'docs',
        'handover_notes',
        'company_assets',
        'handover_document',
        'forwarding_address',
        'forwarding_phone',
        'forwarding_email',
        'need_reference_letter',
        'agree_exit_interview',
        'feedback',
        'approval_status',
        'approved_by',
        'approved_at',
        'approval_notes',
        'users_id',
    ];

    protected $casts = [
        'retirement_date' => 'date',
        'last_working_day' => 'date',
        'approved_at' => 'datetime',
        'need_reference_letter' => 'boolean',
        'agree_exit_interview' => 'boolean',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'users_id');
    }

    public static function getApprovalStatusOptions(): array
    {
        return [
            'pending' => 'Menunggu Persetujuan',
            'approved' => 'Disetujui',
            'rejected' => 'Ditolak',
        ];
    }
}
