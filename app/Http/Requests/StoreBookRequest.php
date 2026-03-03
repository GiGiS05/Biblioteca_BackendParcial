<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBookRequest extends FormRequest
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
            'title' => ['required', 'string'],
            'description' => ['required', 'string'],
            'ISBN' => ['required', 'string', 'regex:/^\d{13}$/', 'unique:books,ISBN'],
            'total_copies' => ['required', 'integer', 'gt:0' , 'gte:available_copies'],
            'available_copies' => ['required', 'integer', 'gte:0'],
            'is_available' => ['required', 'boolean'],            
        ];
    }
}
