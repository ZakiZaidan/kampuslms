<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMaterialRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'   => ['required', 'string', 'max:255'],
            'content' => ['nullable', 'string'],
            'url'     => ['nullable', 'url', 'max:255'],
            'order'   => ['nullable', 'integer', 'min:0'],
            // Batasan 20480 KB (20 MB) dengan validasi MIME sesungguhnya
            'file'    => ['nullable', 'file', 'mimes:pdf,ppt,pptx,doc,docx,zip', 'max:20480'],
        ];
    }
}
