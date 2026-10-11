<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Symfony\Component\HttpFoundation\Response;

class GenerateExternalQrisRequest extends FormRequest
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
            'reference_no' => 'required|string|max:64',
            'amount' => 'required|numeric|min:1000',
            'customer_name' => 'nullable|string|max:100',
            'customer_phone' => 'nullable|string|max:20',
            'description' => 'nullable|string|max:100',
            'company' => 'nullable|string|in:rdi,mcr',
            'channel_code' => 'nullable|string|in:711,715,836',
            'validity_minutes' => 'nullable|integer|min:5|max:1440',
            'format' => 'nullable|string|in:json,image,redirect',
            'callback_url' => 'nullable|url|max:500',
            'callback_token' => 'nullable|string|max:255',
        ];
    }

    /**
     * Handle a failed validation attempt for API.
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'status' => 'error',
            'responseCode' => '4004702',
            'responseMessage' => 'Validation error',
            'errors' => $validator->errors()
        ], Response::HTTP_UNPROCESSABLE_ENTITY));
    }
}
