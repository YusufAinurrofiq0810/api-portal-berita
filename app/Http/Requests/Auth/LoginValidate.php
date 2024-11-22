<?php

namespace App\Http\Requests\Auth;

use app\Helpers\Helper;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Response;

class LoginValidate extends FormRequest
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
            'email' => 'required',
            'password' => 'required'
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'email is required',
            'password.required' => 'password is required'
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(Helper::sendErrorResponse(
            collect($validator->errors())->toArray(),
            Response::HTTP_BAD_REQUEST,
            'Validation Error'
        ));
    }
}
