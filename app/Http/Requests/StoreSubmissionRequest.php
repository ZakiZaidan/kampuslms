<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreSubmissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // BUG 4: Validasi 'extensions' hanya memeriksa string ekstensi nama file.
            // File PHP yang diganti namanya menjadi .pdf akan LOLOS validasi ini.
            // Seharusnya: 'mimes:pdf' — yang membaca byte aktual file.
            //
            // BUG 5: Satuannya KILOBYTE, bukan megabyte.
            // 'max:5' berarti 5 KB, bukan 5 MB.
            // Seharusnya: 'max:5120' untuk batas 5 MB.
            'file' => ['required', 'file', 'extensions:pdf', 'max:5'],
        ];
    }
}
