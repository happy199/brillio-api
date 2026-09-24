<?php

namespace App\Http\Requests\Mentor;

use Illuminate\Foundation\Http\FormRequest;

class ImportLinkedInPdfRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->isMentor() ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'pdf' => ['required', 'file', 'mimes:pdf', 'max:5120'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'pdf.required' => 'Le fichier PDF LinkedIn est obligatoire.',
            'pdf.file' => 'Le fichier téléversé n\'est pas valide.',
            'pdf.mimes' => 'Le fichier doit être un document PDF.',
            'pdf.max' => 'La taille maximale autorisée pour le PDF est de 5 Mo.',
        ];
    }
}
