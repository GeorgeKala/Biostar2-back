<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEmployeeRequest extends FormRequest
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
     * @return array
     */
    public function rules()
    {
        return [
            'fullname' => 'sometimes|string',
            'personal_id' => 'sometimes|string|unique:employees,personal_id,' . $this->route('employee'),
            'phone_number' => 'sometimes|string',
            'department_id' => 'sometimes|exists:departments,id',
            'start_datetime' => 'sometimes|date',
            'expiry_datetime' => 'nullable|date',
            'position' => 'sometimes|string',
            'group_id' => 'sometimes|exists:groups,id',
            'schedule_id' => 'sometimes|exists:schedules,id',
            'honorable_minutes_per_day' => 'sometimes|integer',
            'device' => 'sometimes|string',
            'card_number' => 'sometimes|string',
            'checksum' => 'sometimes|string',
        ];
    }
}
