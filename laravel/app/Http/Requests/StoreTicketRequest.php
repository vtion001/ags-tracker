<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'subject' => ['required', 'string', 'max:255'],
            'category' => ['required', 'in:bug_error,feature_request,schedule_issue,access_problem,other'],
            'priority' => ['required', 'in:low,medium,high'],
            'description' => ['required', 'string', 'max:5000'],
        ];
    }

    public function messages(): array
    {
        return [
            'subject.required' => 'Please enter a subject for your ticket.',
            'category.required' => 'Please select a category.',
            'priority.required' => 'Please select a priority level.',
            'description.required' => 'Please describe your issue or feedback.',
        ];
    }
}
