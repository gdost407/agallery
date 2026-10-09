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
            'name' => ['required', 'string', 'max:255', 'not_regex:~[/\\\\]~', function (string $attribute, mixed $value, \Closure $fail): void {
                if (! $this->filled('parent_id') && in_array(mb_strtolower(trim($value)), ['image', 'video', 'document', 'trash'], true)) {
                    $fail('This name is reserved for a default folder. Choose another name.');
                }
            }],
            'parent_id' => ['nullable', 'integer', Rule::exists('folders', 'id')->where('user_id', $this->user()->id)->whereNull('deleted_at')],
            'password' => ['nullable', 'string', 'min:8', 'max:255', 'confirmed'],
        ];
    }
}
