<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCommentRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'user_id'   => ['required', 'exists:users,id'],
            'text'      => ['required', 'string', 'min:1', 'max:5000'],
            'parent_id' => [
                'nullable',
                'integer',
                'exists:comments,id',
            ],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
