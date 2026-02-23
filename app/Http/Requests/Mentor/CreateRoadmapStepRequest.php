<?php

namespace App\Http\Requests\Mentor;

use App\Models\RoadmapStep;
use Illuminate\Foundation\Http\FormRequest;

class CreateRoadmapStepRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isMentor();
    }

    public function rules(): array
    {
        $stepTypes = implode(',', array_keys(RoadmapStep::STEP_TYPES));

        return [
            'step_type' => ['required', 'string', 'in:'.$stepTypes],
            'title' => ['required', 'string', 'max:255'],
            'institution_company' => ['nullable', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'description' => ['nullable', 'string', 'max:2000'],
            'position' => ['nullable', 'integer', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'step_type.required' => 'Le type d\'étape est obligatoire',
            'step_type.in' => 'Le type d\'étape n\'est pas valide',
            'title.required' => 'Le titre est obligatoire',
            'title.max' => 'Le titre ne peut pas dépasser 255 caractères',
            'end_date.after_or_equal' => 'La date de fin doit être postérieure ou égale à la date de début',
            'description.max' => 'La description ne peut pas dépasser 2000 caractères',
        ];
    }
}
