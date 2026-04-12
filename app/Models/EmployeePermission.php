<?php

namespace App\Models;

use App\Traits\HasUserTracking;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Traits\LogsActivityTrait;

class EmployeePermission extends Model
{
    use LogsActivityTrait;
    use HasUserTracking, SoftDeletes;

    protected $fillable = [
        'employee_id',
        'permission_id',
        'start_permission_date',
        'end_permission_date',
        'permission_desc',
        'scan_doc',
        'approval_status',
        'approved_by',
        'approved_at',
        'approval_notes',
        'is_resign',
        'users_id',
    ];

    protected $casts = [
        'start_permission_date' => 'date',
        'end_permission_date' => 'date',
        'approved_at' => 'datetime',
        'is_resign' => 'boolean',
    ];

    /**
     * Get the employee that owns the permission.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Get the master permission type.
     */
    public function permission(): BelongsTo
    {
        return $this->belongsTo(MasterEmployeePermission::class, 'permission_id');
    }

    /**
     * Get the user who approved this permission.
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get the user who created this record.
     */
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

    public function scopePending($query)
    {
        return $query->where('approval_status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('approval_status', 'approved');
    }

    public function scopeResign($query)
    {
        return $query->where('is_resign', true);
    }
}
