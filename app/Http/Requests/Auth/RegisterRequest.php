<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
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
    public function rules()
    {
        return [
            "first_name"   => "required",
            "last_name"    => "required",
            "country_code" => "required",
            "phone"        => "required",
            'logo'         => 'mimes:jpg,png,jpeg|max:2048',
            "email"        => "required|email|unique:users,email",
            "password"     => "required|min:8|confirmed",
        ];
    }
}
