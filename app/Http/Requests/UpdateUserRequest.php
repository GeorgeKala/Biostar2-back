<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules()
    {
        return [
            'name' => 'sometimes|string|max:255',
            'email' => 'nullable|string|email|max:255|unique:users,email,' . $this->user->id,
            'username' => 'sometimes|string|max:255|unique:users,username,' . $this->user->id,
            'user_type_id' => 'sometimes|exists:user_types,id',
            'department_id' => 'nullable|exists:departments,id',
            'employee_id' => 'nullable|exists:employees,id',
        ];
    }
}
