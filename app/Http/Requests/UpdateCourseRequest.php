<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCourseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $courseId = $this->route('course') instanceof \App\Models\Course 
            ? $this->route('course')->id 
            : $this->route('course');

        return [
            'code'        => ['required', 'string', 'max:20', Rule::unique('courses', 'code')->ignore($courseId)],
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
