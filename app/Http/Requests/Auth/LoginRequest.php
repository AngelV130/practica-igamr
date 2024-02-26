<?php

namespace App\Http\Requests\Auth;
use App\Models\User;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Mail;
use App\Providers\RouteServiceProvider;
use App\Rules\Recaptcha;


use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;

use App\Mail\VerifyCodeAdmin;

class LoginRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
            'captchaResponse' => ['required',new Recaptcha],
        ];
    }
    /**
     * Mensajes de Validaciom
     */
    public function messages(){
        return [
            'required' => 'El campo :attribute  es obligatorio.',
            'string' => 'El campo :attribute debe ser string.',
            'max' => [
                'string' => 'El campo :attribute no debe tener más de :max caracteres.',
            ],
            'min' => [
                'string' => 'El campo :attribute debe tener al menos :min caracteres.',
            ],
            'email' => 'El formato del campo :attribute no es válido.',
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
