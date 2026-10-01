<?php

namespace App\Http\Requests\Master;

use Illuminate\Foundation\Http\FormRequest;

class PartnerInstitutionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && (
            $this->user()->hasRole('SUPERADMIN') ||
            $this->user()->hasPermission('manage:institutions') ||
            $this->user()->hasPermission('manage:master-data')
        );
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string'],
            'contact_person' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'website' => ['nullable', 'url', 'max:255'],
            'sector' => ['required', 'string', 'max:50'],
            'is_active' => ['nullable', 'boolean'],
            'description' => ['nullable', 'string', 'max:1000'],
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
            'name.required' => 'Nama instansi wajib diisi.',
            'address.required' => 'Alamat instansi wajib diisi.',
            'contact_person.required' => 'Kontak PIC / Person In Charge wajib diisi.',
            'email.required' => 'Email resmi instansi wajib diisi.',
            'email.email' => 'Format email instansi tidak valid.',
            'phone.required' => 'Nomor telepon instansi wajib diisi.',
            'website.url' => 'Format URL website harus menyertakan http:// atau https://',
            'sector.required' => 'Sektor/kategori instansi wajib dipilih.',
        ];
    }
}
