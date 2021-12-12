<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UploadPhotoRequest extends FormRequest
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
            'photo' => 'required|mimes:jpg,png,gif,pdf|max:10000',
        ];
    }

    public function failedValidation(Validator $v)
    {
        throw new HttpResponseException(response()->json([
            'status'=> false,
            'message'=> 'Validation error',
            'data'=> $v->errors()
        ]));
    }
}
