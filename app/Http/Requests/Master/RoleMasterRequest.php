<?php

namespace App\Http\Requests\Master;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RoleMasterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && (
            $this->user()->hasRole('SUPERADMIN') ||
            $this->user()->hasPermission('manage:roles')
        );
    }

    public function rules(): array
    {
        $id = $this->route('role')?->id ?? $this->route('role');

        return [
            'name' => [
                'required',
                'string',
                'max:50',
                'alpha_dash',
                Rule::unique('roles', 'name')->ignore($id),
            ],
            'label' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:500'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['exists:permissions,id'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => strtoupper(trim((string) $this->name)),
        ]);
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama role (kode) wajib diisi.',
            'name.unique' => 'Kode role ini sudah digunakan.',
            'name.alpha_dash' => 'Kode role hanya boleh huruf, angka, strip, dan garis bawah.',
            'label.required' => 'Label tampilan role wajib diisi.',
        ];
    }
}
