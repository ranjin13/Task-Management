<?php

namespace App\Http\Requests;

use App\Models\Task;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreSubtaskRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // The actual authorization logic will run after validation in the controller
        // This is to ensure we can get the task_id from the validated data
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
            'task_id' => 'required|integer|exists:tasks,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:1000',
        ];
    }
    
    /**
     * Get custom error messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'task_id.required' => 'The task ID is required.',
            'task_id.exists' => 'The selected task does not exist.',
            'title.required' => 'The subtask title is required.',
            'title.max' => 'The subtask title must not exceed 255 characters.',
            'description.max' => 'The subtask description must not exceed 1000 characters.',
        ];
    }
}
