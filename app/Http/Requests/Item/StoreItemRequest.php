<?php

namespace App\Http\Requests\Item;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'project_id' => ['required', 'uuid', 'exists:projects,id'],
            'category_id' => ['required', 'uuid', Rule::exists('categories', 'id')->whereNull('deleted_at')],
            'subcategory_id' => ['nullable', 'uuid', Rule::exists('subcategories', 'id')->whereNull('deleted_at')],
            'code' => ['required', 'string', 'max:16', 'unique:items,code'],
            'current_status_id' => ['required', 'uuid', 'exists:statuses,id'],
        ];
    }
}
