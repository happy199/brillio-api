<?php

namespace App\Http\Requests\Academic;

use App\Models\AcademicDocument;
use Illuminate\Foundation\Http\FormRequest;

class UploadDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $documentTypes = implode(',', array_keys(AcademicDocument::DOCUMENT_TYPES));

        return [
            'file' => [
                'required',
                'file',
                'max:5120', // 5 Mo en Ko
                'mimes:pdf,jpeg,jpg,png,doc,docx',
            ],
            'document_type' => ['required', 'string', 'in:'.$documentTypes],
            'academic_year' => ['nullable', 'string', 'max:20', 'regex:/^\d{4}-\d{4}$/'],
            'grade_level' => ['nullable', 'string', 'max:50'],
        ];
    }

    public function messages(): array
    {
        return [
            'file.required' => 'Le fichier est obligatoire',
            'file.file' => 'Le fichier n\'est pas valide',
            'file.max' => 'Le fichier ne peut pas dépasser 5 Mo',
            'file.mimes' => 'Le fichier doit être un PDF, une image (JPEG, PNG) ou un document Word',
            'document_type.required' => 'Le type de document est obligatoire',
            'document_type.in' => 'Le type de document n\'est pas valide',
            'academic_year.regex' => 'L\'année scolaire doit être au format AAAA-AAAA (ex: 2023-2024)',
        ];
    }
}
