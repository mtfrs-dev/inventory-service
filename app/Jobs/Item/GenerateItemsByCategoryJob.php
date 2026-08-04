<?php

namespace App\Jobs\Item;

use App\Actions\Item\GenerateItemsByCategoryAction;
use App\Models\Category;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateItemsByCategoryJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public readonly Category $category,
        public readonly int $itemsCount,
        public readonly array $subcategoryData = [],
    ) {
        $this->onQueue('generate-items');
    }

    public function handle(GenerateItemsByCategoryAction $action): void
    {
        $action->handle($this->category, $this->itemsCount, $this->subcategoryData);
    }
}
