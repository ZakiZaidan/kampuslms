<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCourseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code'        => ['required', 'string', 'max:20', 'unique:courses,code'],
            'name'        => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'sks'         => ['required', 'integer', 'between:1,6'],
            'lecturer_id' => ['required', 'exists:users,id'],
            'status'      => ['required', 'in:draft,active,archived'],
        ];
    }

    public function messages(): array
    {
        return [
            'code.required'  => 'Kode mata kuliah wajib diisi.',
            'code.unique'    => 'Kode mata kuliah ini sudah dipakai.',
            'name.required'  => 'Nama mata kuliah wajib diisi.',
            'sks.between'    => 'SKS harus antara 1 sampai 6.',
        ];
    }
}
