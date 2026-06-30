<?php

namespace App\Http\Requests\Role;

use Illuminate\Foundation\Http\FormRequest;

/**
 * SyncPermissionsRequest
 *
 * Validates the permission_ids array for role permission sync.
 * Sync replaces ALL permissions — frontend sends the full desired set.
 */
class SyncPermissionsRequest extends FormRequest
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
            'permission_ids'   => ['required', 'array'],
            'permission_ids.*' => ['uuid', 'exists:permissions,id'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'permission_ids.required'  => 'At least one permission ID is required.',
            'permission_ids.array'     => 'Permission IDs must be an array.',
            'permission_ids.*.integer' => 'Each permission ID must be an integer.',
            'permission_ids.*.exists'  => 'One or more permission IDs do not exist.',
        ];
    }
}
