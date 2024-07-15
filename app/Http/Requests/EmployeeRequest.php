<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EmployeeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize()
    {
        return true; // Set authorization logic if needed
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    
    public function rules()
    {
        return [
            'fullname' => 'required|string',
            'personal_id' => 'required|string|unique:employees,personal_id,' . ($this->employee ? $this->employee->id : 'NULL'),
            'phone_number' => 'required|string',
            'department_id' => 'required|exists:departments,id',
            'start_datetime' => 'required|date',
            'expiry_datetime' => 'nullable|date',
            'position' => 'required|string',
            'group_id' => 'required|exists:groups,id',
            'schedule_id' => 'required|exists:schedules,id',
            'honorable_minutes_per_day' => 'required|integer',
            'device' => 'nullable|string',
            'card_number' => 'required|string',
            'checksum' => 'required|string',
            'holidays' => 'required|array', 
            'holidays.*' => 'exists:holidays,id',
            'device_id' => 'nullable'
        ];
    }
}
