<?php

namespace App\Http\Requests\ApartmentRequest;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
class ApartmentFilterRequest extends FormRequest
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
            'governorate_id' => 'nullable|exists:governorates,id',
            'city_id' => ['nullable',Rule::exists('cities', 'id')->where(function($query){
                if ($this->governorate_id) {
                    $query->where('governorate_id', $this->governorate_id);
                }
            })],
            'price' => 'nullable|numeric',
            'area' => 'nullable|numeric',
            'rooms' => 'nullable|integer',
        ];
    }
}
