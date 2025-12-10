<?php

namespace App\Http\Requests\AuthRequest;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
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
            'phone' => 'required|string|unique:users,phone|regex:/^09[0-9]{8}$/',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'password' => 'required|string|min:8',
            'profile_image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'id_image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'birth_date' => 'required|date|before_or_equal:18 years ago',
        ];
    }
}
