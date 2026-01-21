<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

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
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Sanitize email input - trim whitespace and convert to lowercase
        if ($this->has('email')) {
            $this->merge([
                'email' => strtolower(trim($this->input('email')))
            ]);
        }
        
        // Ensure password is trimmed but preserve case sensitivity
        if ($this->has('password')) {
            $this->merge([
                'password' => trim($this->input('password'))
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $emailRules = [
            'required',
            'string',
            'email', // Basic email validation
            'max:255',
        ];

        // Add stricter validation only in production
        if (app()->environment('production')) {
            $emailRules[] = 'email:rfc,dns'; // Enhanced email validation with DNS check
            $emailRules[] = 'regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/'; // Additional email format validation
        }

        return [
            'email' => $emailRules,
            'password' => [
                'required',
                'string',
                'min:8',
                'max:255', // Prevent extremely long passwords that could cause DoS
            ]
        ];
    }

    /**
     * Get custom error messages for validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'email.required' => 'Email address is required.',
            'email.email' => 'Please provide a valid email address.',
            'email.max' => 'Email address must not exceed 255 characters.',
            'email.regex' => 'Please provide a valid email address format.',
            'password.required' => 'Password is required.',
            'password.min' => 'Password must be at least 8 characters long.',
            'password.max' => 'Password must not exceed 255 characters.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'email' => 'email address',
            'password' => 'password',
        ];
    }
}