<?php

namespace App\Http\Requests\Personality;

use Illuminate\Foundation\Http\FormRequest;

class SubmitTestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'responses' => ['required', 'array', 'min:20', 'max:20'],
            'responses.*' => ['required', 'integer', 'min:-3', 'max:3'],
        ];
    }

    public function messages(): array
    {
        return [
            'responses.required' => 'Les réponses sont obligatoires',
            'responses.array' => 'Les réponses doivent être un tableau',
            'responses.min' => 'Toutes les 20 questions doivent être répondues',
            'responses.max' => 'Le test contient exactement 20 questions',
            'responses.*.required' => 'Chaque réponse est obligatoire',
            'responses.*.integer' => 'Chaque réponse doit être un nombre entier',
            'responses.*.min' => 'La valeur minimale est -3',
            'responses.*.max' => 'La valeur maximale est 3',
        ];
    }
}
