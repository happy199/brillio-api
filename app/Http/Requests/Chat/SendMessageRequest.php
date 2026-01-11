<?php

namespace App\Http\Requests\Chat;

use Illuminate\Foundation\Http\FormRequest;

class SendMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'conversation_id' => ['nullable', 'integer', 'exists:chat_conversations,id'],
            'message' => ['required', 'string', 'min:1', 'max:5000'],
        ];
    }

    public function messages(): array
    {
        return [
            'conversation_id.exists' => 'Cette conversation n\'existe pas',
            'message.required' => 'Le message est obligatoire',
            'message.max' => 'Le message ne peut pas dépasser 5000 caractères',
        ];
    }
}
