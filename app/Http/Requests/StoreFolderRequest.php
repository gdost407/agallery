<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreFolderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', 'not_regex:~[/\\\\]~'],
            'parent_id' => ['nullable', 'integer', Rule::exists('folders', 'id')->where('user_id', $this->user()->id)->whereNull('deleted_at')],
            'password' => ['nullable', 'string', 'min:8', 'max:255', 'confirmed'],
        ];
    }
}
