<?php

namespace App\Http\Requests\Master;

use Illuminate\Foundation\Http\FormRequest;

class UserRoleAssignRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && (
            $this->user()->hasRole('SUPERADMIN') ||
            $this->user()->hasPermission('manage:roles') ||
            $this->user()->hasPermission('manage:users')
        );
    }

    public function rules(): array
    {
        return [
            'roles' => ['required', 'array'],
            'roles.*' => ['exists:roles,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'roles.required' => 'Pilih minimal satu role untuk pengguna ini.',
            'roles.array' => 'Format role tidak valid.',
            'roles.*.exists' => 'Role yang dipilih tidak ditemukan dalam sistem.',
        ];
    }
}
