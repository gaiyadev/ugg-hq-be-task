<?php

namespace App\Http\Requests\Resource;

use App\Enums\ResourceStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateResourceRequest extends FormRequest
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
            'title'       => ['sometimes', 'string', 'min:3', 'max:255'],
            'description' => ['nullable', 'string', 'max:10000'],
            // Status via PUT is limited — workflow transitions use dedicated endpoints
            'status'      => [
                'sometimes',
                'string',
                Rule::in(array_column(ResourceStatus::cases(), 'value')),
            ],
        ];
    }
}
