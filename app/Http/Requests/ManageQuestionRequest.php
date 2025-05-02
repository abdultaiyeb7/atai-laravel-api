<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ManageQuestionRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'action_type' => 'required|in:I,U',
            'p_id' => 'nullable|integer',
            'p_question_text' => 'required_if:action_type,I|string',
            'p_question_label' => 'nullable|string|max:500',
            'p_question_type' => 'required|integer|in:1,2,3,4,5,6',
            'p_client_id' => 'required|integer',
            'p_question_level' => 'nullable|integer',
            'p_question_parent_level' => 'nullable|integer',
        ];
    }
}
