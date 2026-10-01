<?php

namespace App\Http\Requests\Master;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserMasterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && (
            $this->user()->hasRole('SUPERADMIN') ||
            $this->user()->hasPermission('manage:users')
        );
    }

    public function rules(): array
    {
        $id = $this->route('user')?->id ?? $this->route('user');
        $isCreate = $this->isMethod('post');

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($id),
            ],
            'password' => [$isCreate ? 'required' : 'nullable', 'string', 'min:8', 'confirmed'],
            'study_program_id' => ['nullable', 'exists:study_programs,id'],
            'identifier_number' => ['nullable', 'string', 'max:50'],
            'phone' => ['nullable', 'string', 'max:30'],
            'is_active' => ['nullable', 'boolean'],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['exists:roles,id'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->boolean('is_active'),
        ]);
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama pengguna wajib diisi.',
            'email.required' => 'Alamat email wajib diisi.',
            'email.email' => 'Format alamat email tidak valid.',
            'email.unique' => 'Email ini sudah digunakan oleh akun lain.',
            'password.required' => 'Password wajib diisi untuk pengguna baru.',
            'password.min' => 'Password minimal terdiri dari 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'study_program_id.exists' => 'Program studi yang dipilih tidak valid.',
        ];
    }
}
