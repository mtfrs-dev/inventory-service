<?php

namespace App\Http\Requests\Item;

use App\Models\Item;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class BulkUpdateItemSerialNumberRequest extends FormRequest
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
            'items.*.serial_number' => ['required', 'string', 'max:255'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $items = collect($this->input('items', []));

            $serialNumbers = $items->pluck('serial_number')->filter()->values();
            $ids = $items->pluck('id')->filter()->values();

            if ($serialNumbers->count() !== $serialNumbers->unique()->count()) {
                $validator->errors()->add('items', 'Serial number yang dikirimkan harus unik.');

                return;
            }

            $takenSerials = Item::whereIn('serial_number', $serialNumbers->all())
                ->whereNotIn('id', $ids->all())
                ->pluck('serial_number');

            foreach ($items as $item) {
                if ($takenSerials->contains($item['serial_number'] ?? null)) {
                    $validator->errors()->add(
                        "{$item['serial_number']}",
                        'Serial number telah digunakan.'
                    );
                }
            }
        });
    }
}
