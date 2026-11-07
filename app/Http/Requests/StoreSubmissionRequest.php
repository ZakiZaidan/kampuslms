<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSubmissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Memeriksa MIME type sesungguhnya dan batasan 5120 KB (5 MB)
            'file' => ['required', 'file', 'mimes:pdf,doc,docx,zip,rar', 'max:5120'],
            'note' => ['nullable', 'string', 'max:500'],
        ];
    }
}
