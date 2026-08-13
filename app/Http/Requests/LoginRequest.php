<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    /// Determine if the user is authorized to make this request.
    public function authorize(): bool
    {
        return true; // Allow all users to make this request
    }

    /// Get the validation rules that apply to the request.
    // Aturan validasi untuk login, pastikan email dan password diisi dengan benar
    public function rules(): array
    {
        return [
            'email' => 'required|email',
            'password' => 'required|string',
        ];
    }
}
