<?php

namespace App\Http\Requests\Item;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GenerateItemsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $parentExistsRule = $this->input('entity_type') === 'subcategory'
            ? Rule::exists('categories', 'id')->whereNull('deleted_at')
            : Rule::exists('projects', 'id');

        return [
            'entity_type'          => ['required', Rule::in(['category', 'subcategory'])],
            'parent_id'            => ['nullable', 'uuid', 'required_without:external_parent_id', $parentExistsRule],
            'external_parent_id'   => ['nullable', 'integer', 'required_without:parent_id'],
            'entity_id'            => ['nullable', 'uuid', 'required_without:external_entity_id'],
            'external_entity_id'   => ['nullable', 'integer', 'required_without:entity_id'],
            'items_count'          => ['required', 'integer', 'min:1', 'max:10000'],
        ];
    }
}
