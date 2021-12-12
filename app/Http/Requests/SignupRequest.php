<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class SignupRequest extends FormRequest
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
        return [
            //
            'profile_pic' => 'required|mimes:jpg,png,|max:10000',
            'name' => 'required|Alpha|between:2,100',
            'email' => 'required|string|email:rfc,dns|max:100|unique:users',
            'password' => 'required|string|confirmed|min:6',
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
