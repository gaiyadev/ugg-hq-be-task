<?php

namespace App\Http\Requests\Resource;

use App\Enums\ResourceStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateResourceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title'       => ['required', 'string', 'min:3', 'max:255'],
            'description' => ['nullable', 'string', 'max:10000'],
            'status'      => [
                'sometimes',
                'string',
                Rule::in([ResourceStatus::Draft->value, ResourceStatus::Pending->value]),
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Resource title is required.',
            'title.min'      => 'Title must be at least 3 characters.',
            'status.in'      => 'New resources can only be created as draft or pending.',
        ];
    }
}
