<?php

namespace App\Http\Requests\Item;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BulkUpdateItemStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'items' => ['required', 'array', 'min:1', 'max:100'],
            'items.*.id' => ['required', 'uuid', Rule::exists('items', 'id')->whereNull('deleted_at')],
            'items.*.status_id' => ['required', 'uuid', 'exists:statuses,id'],
            'items.*.reason' => ['sometimes', 'nullable', 'string', 'max:1000'],
            'items.*.location_name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'items.*.location_address' => ['sometimes', 'nullable', 'string', 'max:1000'],
            'items.*.updated_by' => ['sometimes', 'nullable', 'uuid', 'exists:users,id'],
            'items.*.attachments' => ['sometimes', 'array', 'max:10'],
            'items.*.attachments.*' => ['file', 'max:10240', 'mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx'],
        ];
    }
}
