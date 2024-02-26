<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\SecurePassword;
use App\Rules\Recaptcha;
use Illuminate\Validation\ValidationException;


use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;

class UserRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:users',
            'password' => ['required', 'confirmed', new SecurePassword],
            'captchaResponse' => ['required',new Recaptcha]
        ];
    }
    /**
     * Mensajes de Validaciom
     */
    public function messages(){
        return [
            'required' => 'El campo :attribute es obligatorio.',
            'max' => [
                'numeric' => 'El campo :attribute no debe ser mayor que :max.',
                'string' => 'El campo :attribute no debe tener más de :max caracteres.',
            ],
            'min' => [
                'numeric' => 'El campo :attribute debe ser al menos :min.',
                'string' => 'El campo :attribute debe tener al menos :min caracteres.',
            ],
            'numeric' => 'El campo :attribute debe ser un número.',
            'email' => 'El formato del campo :attribute no es válido.',
            'unique' => 'El valor del campo :attribute ya está en uso.',
            'confirmed' => 'La confirmación del campo :attribute no coincide con el valor proporcionado.',
        ];
    }


    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'errors' => $validator->errors(),
            'status' => JsonResponse::HTTP_UNPROCESSABLE_ENTITY
        ], JsonResponse::HTTP_UNPROCESSABLE_ENTITY));
    }
}
