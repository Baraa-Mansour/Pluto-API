<?php

namespace App\Http\Requests\ApartmentRequest;

use Illuminate\Foundation\Http\FormRequest;

class ApartmentCreateRequest extends FormRequest
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
            'city_id' => 'required|exists:cities,id',
            'rooms' => 'required|integer|min:1',
            'area' => 'required|integer|min:50',
            'price' => 'required|numeric|min:0',
            'photo' => 'required|image|mimes:jpeg,png,jpg,svg|max:2048',
            'floor' => 'required|integer|min:0',
            'description' => 'required|string|max:255',
        ];
    }
}
