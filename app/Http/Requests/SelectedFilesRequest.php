<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

class SelectedFilesRequest extends BulkDeleteFilesRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            ...parent::rules(),
            'action' => ['required', Rule::in(['delete', 'copy', 'move', 'share', 'download'])],
            'folder_id' => [Rule::when(in_array($this->input('action'), ['copy', 'move'], true), ['present']),
                'nullable', 'integer', Rule::exists('folders', 'id')->where('user_id', $this->user()->id)->whereNull('deleted_at')],
        ];
    }
}
