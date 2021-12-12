<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
    // dd(request()->all());
        return [
            //
            'profile_pic' => 'mimes:jpg,png,|max:10000',
            'name' => 'Alpha|string|between:2,100',
            'email' => 'string|email:rfc,dns|max:100|unique:users',
            'password' => 'string|min:6',
        ];
    }

    public function failedValidation(Validator $v)
    {
        throw new HttpResponseException(response()->json([
            'status' => false,
            'message' => 'Validation error',
            'data' => $v->errors()
        ]));
    }

    public function messages()
    {
        return [
            'profile_pic.required' => 'Picture is required',
            'email.required' => 'An email is required',
        ];
    }
}
