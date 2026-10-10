<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Rule;

class UploadFilesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $fileRules = ['required', 'file', 'max:524288'];
        $fileRules[] = function (string $attribute, mixed $value, \Closure $fail): void {
            if ($value instanceof UploadedFile
                && (mb_strlen($value->getClientOriginalName()) > 255 || mb_strlen($value->getClientOriginalExtension()) > 32)) {
                $fail('The filename or extension is too long.');
            }
        };
        if ($this->routeIs('app.photos.store')) {
            $fileRules[] = 'mimes:jpg,jpeg,png,gif,webp,bmp,avif,heic,heif';
        } elseif ($this->routeIs('app.videos.store')) {
            $fileRules[] = 'mimes:mp4,mov,avi,mkv,webm,mpeg,mpg,m4v,3gp';
        } elseif ($this->routeIs('app.documents.store')) {
            $fileRules[] = 'mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,txt,csv,rtf,odt,ods,odp';
        }
        if ($this->boolean('sync')) {
            $fileRules[] = 'mimes:jpg,jpeg,png,gif,webp,bmp,avif,heic,heif,mp4,mov,avi,mkv,webm,mpeg,mpg,m4v,3gp';
        }

        return [
            'files' => ['required', 'array', 'min:1', 'max:20'],
            'files.*' => $fileRules,
            'folder_id' => ['nullable', 'integer', Rule::exists('folders', 'id')->where('user_id', $this->user()->id)->whereNull('deleted_at')],
            'sync' => ['sometimes', 'boolean'],
            'password' => ['prohibited'],
            'password_confirmation' => ['prohibited'],
        ];
    }
}
