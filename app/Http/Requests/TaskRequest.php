<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TaskRequest extends FormRequest
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
        $task = request()->route('task');
        $taskId = $task ? $task->id : null;

        return [
            'title' => [
                'required',
                'string',
                'max:100',
                $taskId ? Rule::unique('tasks')->ignore($taskId) : Rule::unique('tasks')
            ],
            'description' => 'required|string',
            'status' => 'required|in:to-do,in-progress,done',
            'is_published' => 'boolean',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:4096', // 4MB max
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'title' => 'task title',
            'description' => 'task description',
            'status' => 'task status',
            'is_published' => 'publication status',
            'image' => 'task image',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.required' => 'The task title is required.',
            'title.max' => 'The task title must not exceed 100 characters.',
            'title.unique' => 'This task title already exists. Please choose a different title.',
            'description.required' => 'The task description is required.',
            'status.required' => 'The task status is required.',
            'status.in' => 'The task status must be one of: to-do, in-progress, done.',
            'image.image' => 'The file must be an image.',
            'image.mimes' => 'The image must be a file of type: jpeg, png, jpg, gif, svg.',
            'image.max' => 'The image must not exceed 4MB.',
        ];
    }
} 