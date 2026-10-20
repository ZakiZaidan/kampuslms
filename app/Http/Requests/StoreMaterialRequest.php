<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreMaterialRequest extends FormRequest
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
            'title' => ['required', 'string', 'max:150'],

            // BUG 4: Validasi 'extensions' hanya memeriksa string ekstensi nama file.
            // File PHP berekstensi .pdf akan LOLOS. Seharusnya: 'mimes:pdf,doc,docx,ppt,pptx'
            //
            // BUG 5: Satuan max adalah KILOBYTE.
            // 'max:20' = 20 KB, bukan 20 MB. Seharusnya: 'max:20480'
            'file' => ['required_without:url', 'nullable', 'file', 'extensions:pdf,doc,docx,ppt,pptx', 'max:20'],

            'url'  => ['required_without:file', 'nullable', 'url'],
        ];
    }
}
