<?php

namespace App\Http\Requests\Webhook;

use Illuminate\Foundation\Http\FormRequest;

class ProjectUpsertedRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id'              => ['required', 'string'],
            'name'            => ['required', 'string', 'max:255'],
            'code'            => ['required', 'string', 'max:255'],
            'year'            => ['required', 'string', 'max:4'],
            'pic_external_id' => ['nullable', 'string'],
        ];
    }
}
