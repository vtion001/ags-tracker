<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($this->user()->id),
            ],
            // Basic Profile (self-service editable)
            'position' => ['nullable', 'string', 'max:100'],
            'contact_number' => ['nullable', 'string', 'max:20'],
            'shift_schedule' => ['nullable', 'string', 'max:50'],
            'hire_date' => ['nullable', 'date'],
            'work_location' => ['nullable', 'in:office,wfh,hybrid'],
            'manager_name' => ['nullable', 'string', 'max:100'],
            // Emergency Contact
            'emergency_contact_name' => ['nullable', 'string', 'max:100'],
            'emergency_contact_phone' => ['nullable', 'string', 'max:20'],
            'emergency_contact_relationship' => ['nullable', 'string', 'max:50'],
            // These require admin role to edit
            'department' => ['nullable', 'string', 'max:100'],
            'tl_email' => ['nullable', 'email', 'max:255'],
        ];
    }
}
