<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCustomerRequest extends FormRequest
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
     * @return array<string, mixed>
     */
    // public function rules()
    // {
    //     return [
    //         //
    //     ];
    // }

      public function rules(): array
    {
        return [

            'first_name' => 'required|string|max:50|regex:/^[\pL\s\-]+$/u',
            'last_name' => 'required|string|max:50|regex:/^[\pL\s\-]+$/u',
            
            'email' => email_enabled() ? [
            'required',
            'email',
            'unique:customers,email',
            ] : ['nullable', 'email'],
            
            // 'email' => 'required|email|unique:customers,email',
            'username' => 'required|unique:customers,username|regex:/^[a-zA-Z0-9_\-\.@]+$/',
            'phone_full' => [
                'required',
                'regex:/^\+?[1-9][0-9]{6,15}$/',
                'unique:customers,phone_number',
                function ($attribute, $value, $fail) {
                    if (preg_match('/(.)\1{5,}/', $value)) {
                        $fail("The phone number must not contain repeated digits.");
                    }
                }
            ],
            'customerpassword' => [
                'required',
                'string',
                'min:4',
                'max:39',
                'regex:/^[a-zA-Z0-9_\-@.]+$/',
                function ($attribute, $value, $fail) {
                    $lower = strtolower($value);

                    if (preg_match('/(.)\1{3,}/', $lower)) {
                        return $fail("Password must not contain repeated characters.");
                    }

                    $sequence = 'abcdefghijklmnopqrstuvwxyz0123456789';
                    for ($i = 0; $i <= strlen($sequence) - 4; $i++) {
                        if (strpos($lower, substr($sequence, $i, 4)) !== false) {
                            $fail("Password must not contain sequential characters.");
                            break;
                        }
                    }
                }
            ],
            'country_code' => 'nullable|string|size:2',
            'timezone' => 'nullable|string|max:50',
            'ip_address' => 'nullable|ip',
        ];
    }

    public function messages(): array
    {
        return [
            
            // First Name
            'first_name.required' => 'First name is required.',
            'first_name.string' => 'First name must be a valid string.',
            'first_name.max' => 'First name cannot exceed 50 characters.',
            'first_name.regex' => 'First name can only contain letters, spaces, and hyphens.',

            // Last Name
            'last_name.required' => 'Last name is required.',
            'last_name.string' => 'Last name must be a valid string.',
            'last_name.max' => 'Last name cannot exceed 50 characters.',
            'last_name.regex' => 'Last name can only contain letters, spaces, and hyphens.',

            // Email
            'email.required' => 'Email is required.',
            'email.email' => 'Please enter a valid email address.',
            'email.unique' => 'This email is already registered.',

            // Username
            'username.required' => 'Username is required.',
            'username.unique' => 'This username is already taken.',
            'username.regex' => 'Username can only contain letters, numbers, dashes, underscores, dots, and @ symbol.',

            // Phone
            'phone_full.required' => 'Phone number is required.',
            'phone_full.regex' => 'Please enter a valid international phone number (e.g., +441234567890).',
            'phone_full.unique' => 'This phone number is already in use.',

            // Password
            'customerpassword.required' => 'Password is required.',
            'customerpassword.string' => 'Password must be a valid string.',
            'customerpassword.min' => 'Password must be at least 4 characters.',
            'customerpassword.max' => 'Password cannot exceed 39 characters.',
            'customerpassword.regex' => 'Password can only contain letters, numbers, dashes, underscores, dots, and @ symbol.',

            // Optional
            'country_code.size' => 'Country code must be exactly 2 characters.',
            'timezone.max' => 'Timezone must not exceed 50 characters.',
            'ip_address.ip' => 'Please enter a valid IP address.',
        ];
    }
}
