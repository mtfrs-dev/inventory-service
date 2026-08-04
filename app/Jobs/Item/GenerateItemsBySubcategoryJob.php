<?php

namespace App\Jobs\Item;

use App\Actions\Item\GenerateItemsBySubcategoryAction;
use App\Models\Subcategory;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateItemsBySubcategoryJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public readonly Subcategory $subcategory,
        public readonly int $itemsCount,
    ) {
        $this->onQueue('generate-items');
    }

    public function handle(GenerateItemsBySubcategoryAction $action): void
    {
        $action->handle($this->subcategory, $this->itemsCount);
    }
}
