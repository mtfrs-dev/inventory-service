<?php

namespace App\Http\Requests\Item;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FilterItemsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'project_id'                => ['sometimes', 'nullable', 'uuid', 'exists:projects,id'],
            'external_project_id'       => ['sometimes', 'nullable', 'integer', 'exists:projects,external_id'],
            'category_id'               => ['sometimes', 'nullable', 'uuid', Rule::exists('categories', 'id')->whereNull('deleted_at')],
            'external_category_id'      => ['sometimes', 'nullable', 'integer', Rule::exists('categories', 'external_id')->whereNull('deleted_at')],
            'subcategory_id'            => ['sometimes', 'nullable', 'uuid', Rule::exists('subcategories', 'id')->whereNull('deleted_at')],
            'external_subcategory_id'   => ['sometimes', 'nullable', 'integer', Rule::exists('subcategories', 'external_id')->whereNull('deleted_at')],
            'user_id'                   => ['sometimes', 'nullable', 'uuid'],
            'trashed'                   => ['sometimes', 'nullable', 'string', Rule::in(['with', 'only'])],
            'status'                    => ['sometimes', 'nullable', 'string', 'exists:statuses,code'],
            'search'                    => ['sometimes', 'nullable', 'string', 'max:100'],
            'sort'                      => ['sometimes', 'nullable', 'string', 'in:code,serial_number,created_at,updated_at'],
            'direction'                 => ['sometimes', 'nullable', 'string', 'in:asc,desc'],
            'per_page'                  => ['sometimes', 'nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}
