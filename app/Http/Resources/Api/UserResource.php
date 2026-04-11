<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $employee = $this->employee;

        if (!$employee) {
            return [
                'id' => $this->id,
                'name' => $this->name,
                'email' => $this->email,
                'is_verified' => (bool)$this->is_verified,
                'profile' => null,
            ];
        }

        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'is_verified' => (bool)$this->is_verified,
            'roles' => $this->roles->pluck('name'),
            'profile' => [
                'personal_data' => [
                    'nippam' => $employee->nippam,
                    'name' => $employee->name,
                    'email' => $employee->email,
                    'office_email' => $employee->office_email,
                    'phone_number' => $employee->phone_number,
                    'address' => $employee->address,
                    'gender' => $employee->gender,
                    'place_birth' => $employee->place_birth,
                    'date_birth' => $employee->date_birth?->format('Y-m-d'),
                    'religion' => $employee->religion,
                    'blood_type' => $employee->blood_type,
                    'marital_status' => $employee->marital_status,
                    'image_url' => $employee->image ? asset('storage/' . $employee->image) : null,
                ],
                'employment_data' => [
                    'employment_status' => $employee->employmentStatus?->name,
                    'position' => $employee->position?->name,
                    'department' => $employee->department?->name,
                    'sub_department' => $employee->subDepartment?->name,
                    'bagian' => $employee->bagian?->name,
                    'cabang' => $employee->cabang?->name,
                    'unit' => $employee->unit?->name,
                    'grade' => $employee->grade?->name,
                    'mkg_years' => $employee->serviceGrade?->service_grade,
                    'entry_date' => $employee->entry_date?->format('Y-m-d'),
                    'probation_appointment_date' => $employee->probation_appointment_date?->format('Y-m-d'),
                    'length_service' => $employee->formatted_length_service,
                    'retirement_date' => $employee->retirement?->format('Y-m-d'),
                    'leave_balance' => $employee->leave_balance,
                ],
            ],
        ];
    }
}
