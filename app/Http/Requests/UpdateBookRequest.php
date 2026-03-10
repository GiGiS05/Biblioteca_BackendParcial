<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBookRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'required', 'string'],
            'description' => ['sometimes', 'required', 'string'],
            'ISBN' => ['sometimes', 'required', 'string', 'regex:/^\d{13}$/', 'unique:books,ISBN'],
            'total_copies' => ['sometimes', 'required', 'integer', 'gt:0' , 'gte:available_copies'],
            'available_copies' => ['sometimes', 'required', 'integer', 'gte:0'],
            'is_available' => ['sometimes', 'required', 'boolean'],            
        ];
    }
}
