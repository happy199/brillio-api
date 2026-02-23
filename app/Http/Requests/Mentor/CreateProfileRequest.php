<?php

namespace App\Http\Requests\Mentor;

use App\Models\MentorProfile;
use Illuminate\Foundation\Http\FormRequest;

class CreateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isMentor();
    }

    public function rules(): array
    {
        $specializations = implode(',', array_keys(MentorProfile::SPECIALIZATIONS));

        return [
            'bio' => ['nullable', 'string', 'max:2000'],
            'current_position' => ['nullable', 'string', 'max:255'],
            'current_company' => ['nullable', 'string', 'max:255'],
            'years_of_experience' => ['nullable', 'integer', 'min:0', 'max:50'],
            'specialization' => ['nullable', 'string', 'in:'.$specializations],
        ];
    }

    public function messages(): array
    {
        return [
            'bio.max' => 'La bio ne peut pas dépasser 2000 caractères',
            'current_position.max' => 'Le poste actuel ne peut pas dépasser 255 caractères',
            'current_company.max' => 'L\'entreprise actuelle ne peut pas dépasser 255 caractères',
            'years_of_experience.min' => 'L\'expérience ne peut pas être négative',
            'years_of_experience.max' => 'L\'expérience ne peut pas dépasser 50 ans',
            'specialization.in' => 'La spécialisation n\'est pas valide',
        ];
    }
}
