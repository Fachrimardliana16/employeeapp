<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeDocument extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'employee_id',
        'document_type',
        'document_number',
        'document_name',
        'file_path',
        'issue_date',
        'expiry_date',
        'notes',
        'uploaded_by',
        'users_id',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'expiry_date' => 'date',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'users_id');
    }

    public static function getDocumentTypeOptions(): array
    {
        return [
            'KTP' => 'KTP',
            'KK' => 'Kartu Keluarga',
            'Ijazah' => 'Ijazah',
            'Transkrip' => 'Transkrip Nilai',
            'Sertifikat' => 'Sertifikat',
            'NPWP' => 'NPWP',
            'BPJS Kesehatan' => 'BPJS Kesehatan',
            'BPJS Ketenagakerjaan' => 'BPJS Ketenagakerjaan',
            'Surat Keterangan Sehat' => 'Surat Keterangan Sehat',
            'SKCK' => 'SKCK',
            'Pas Foto' => 'Pas Foto',
            'CV' => 'Curriculum Vitae',
            'Surat Lamaran' => 'Surat Lamaran',
            'Lainnya' => 'Lainnya',
        ];
    }

    public function isExpired(): bool
    {
        if (!$this->expiry_date) {
            return false;
        }
        return $this->expiry_date < now();
    }
}
