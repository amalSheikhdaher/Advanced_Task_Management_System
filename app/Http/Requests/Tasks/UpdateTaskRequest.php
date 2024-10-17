<?php

namespace App\Http\Requests\Tasks;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTaskRequest extends FormRequest
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
            'title' => 'sometimes|required|string|max:255', // Title is optional, must be a string with a max length of 255
            'description' => 'sometimes|nullable|string',    // Description can be null, or a string
            'type' => 'sometimes|in:Bug,Feature,Improvement',  // Task type is optional but must be one of the listed values
            'status' => 'sometimes|nullable|string|in:Open,In Progress,Completed,Blocked', // Status is optional but must match one of the statuses
            'priority' => 'sometimes|in:Low,Medium,High', // Priority is optional but must be Low, Medium, or High
            'due_date' => 'nullable|date|after_or_equal:today', // Due date can be null, must be a valid date after today if provided
            'assigned_to' => 'sometimes|nullable|exists:users,id', 
        ];
    }

    /**
     * Custom messages for validation errors (optional).
     * You can customize the error messages for each rule here.
     */
    public function messages(): array
    {
        return [
            'title.max' => 'The title may not be greater than 255 characters.',
            'type.in' => 'The task type must be one of: Bug, Feature, Improvement.',
            'status.in' => 'The task status must be one of: Open, In Progress, Completed, Blocked.',
            'priority.in' => 'The task priority must be one of: Low, Medium, High.',
            'due_date.after_or_equal' => 'The due date must be today or a future date.',
            'assigned_to.exists' => 'The selected user does not exist.',
        ];
    }
}
