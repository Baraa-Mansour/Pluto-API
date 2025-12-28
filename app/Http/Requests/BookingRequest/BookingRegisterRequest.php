<?php

namespace App\Http\Requests\BookingRequest;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BookingRegisterRequest extends FormRequest
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
            'apartment_id' => [
                'required',
                Rule::exists('apartments' , 'id')
                ->where(function ($query) 
                {
                    $query->whereNotIn('user_id', function ($sub) 
                    {
                        $sub->select('id')
                            ->from('users')
                            ->where('status' , [User::STATUS_DELETED,User::STATUS_REGISTERED]);
                    });
                }),
            ], // check if apartment deleted
            'from_date' => 'required|date|after:tomorrow', // from date should be at least tomorrow
            'to_date' => 'required|date|after_or_equal:from_date',
            //'payment_method_id' => 'required|exists:payment_methods,id'
        ];
    }
}
