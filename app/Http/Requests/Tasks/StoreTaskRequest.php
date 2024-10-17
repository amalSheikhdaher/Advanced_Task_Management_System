<?php

namespace App\Http\Requests\Tasks;

use Illuminate\Foundation\Http\FormRequest;

class StoreTaskRequest extends FormRequest
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
    public function rules(): array
    {
        return [
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'type'        => 'required|in:Bug,Feature,Improvement',
            'status'      => 'required|in:Open,In Progress,Completed,Blocked',
            'priority'    => 'required|in:Low,Medium,High',
            'due_date'    => 'nullable|date|after_or_equal:today',
            'assigned_to' => 'nullable|exists:users,id'
        ];
    }

    /**
     * Custom error messages for validation failures.
     * 
     * @return array
     */
    public function messages(): array
    {
        return [
            'title.required'          => 'The task title is required.',
            'type.required'           => 'The task type is required and must be either Bug, Feature, or Improvement.',
            'status.required'         => 'The task status is required and must be Open, In Progress, Completed, or Blocked.',
            'priority.required'       => 'You must specify the task priority (Low, Medium, or High).',
            'due_date.after_or_equal' => 'The due date must be today or a future date.',
            'assigned_to.exists'      => 'The assigned user must exist in the system.'
        ];
    }
}
