<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ResourcePasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'password' => $this->routeIs('app.files.password') ? ['prohibited'] : ['nullable', 'string', 'min:8', 'max:255', 'confirmed'],
            'current_password' => ['nullable', 'string', 'max:255'],
        ];
    }
}
