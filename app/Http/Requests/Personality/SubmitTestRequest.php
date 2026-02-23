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
            'personality_type' => ['required', 'string', 'size:4'],
            'personality_label' => ['required', 'string', 'max:255'],
            'personality_description' => ['required', 'string'],
            'traits_scores' => ['required', 'array'],
            'traits_scores.E' => ['required', 'numeric', 'min:0', 'max:100'],
            'traits_scores.I' => ['required', 'numeric', 'min:0', 'max:100'],
            'traits_scores.S' => ['required', 'numeric', 'min:0', 'max:100'],
            'traits_scores.N' => ['required', 'numeric', 'min:0', 'max:100'],
            'traits_scores.T' => ['required', 'numeric', 'min:0', 'max:100'],
            'traits_scores.F' => ['required', 'numeric', 'min:0', 'max:100'],
            'traits_scores.J' => ['required', 'numeric', 'min:0', 'max:100'],
            'traits_scores.P' => ['required', 'numeric', 'min:0', 'max:100'],
            'responses' => ['required', 'array', 'min:32', 'max:32'],
            'responses.*' => ['required', 'integer', 'min:1', 'max:5'],
        ];
    }

    public function messages(): array
    {
        return [
            'personality_type.required' => 'Le type de personnalité est obligatoire',
            'personality_type.size' => 'Le type de personnalité doit contenir exactement 4 caractères',
            'personality_label.required' => 'Le label de personnalité est obligatoire',
            'personality_description.required' => 'La description de personnalité est obligatoire',
            'traits_scores.required' => 'Les scores des traits sont obligatoires',
            'responses.required' => 'Les réponses sont obligatoires',
            'responses.min' => 'Toutes les 32 questions doivent être répondues',
            'responses.max' => 'Le test contient exactement 32 questions',
            'responses.*.min' => 'La valeur minimale est 1',
            'responses.*.max' => 'La valeur maximale est 5',
        ];
    }
}
