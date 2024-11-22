<?php

namespace App\Http\Requests\Auth;

use app\Helpers\Helper;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Response;

class RegisterValidate extends FormRequest
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
            'name' => 'required',
            'password' => 'required',
            'confirm_password' => 'required|same:password'
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
