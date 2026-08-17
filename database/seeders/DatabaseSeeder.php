<?php

namespace Database\Seeders;

use App\Actions\Item\GenerateItemQrCodeAction;
use App\Models\Category;
use App\Models\Item;
use App\Models\Project;
use App\Models\Status;
use App\Models\Subcategory;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // fakerphp/faker (used by User::factory()) is a require-dev package.
        // deploy.sh always runs `composer install --no-dev` on VM02, so it's
        // never present there regardless of what APP_ENV is set to — gate on
        // 'local', not on excluding 'production', so this stays correct no
        // matter how the server's .env is labeled.
        if (app()->environment('local')) {
            User::factory()
                ->count(10)
                ->create();
        }

        $this->call(StatusSeeder::class);

        // $projects = Project::factory()
        //     ->count(5)
        //     ->create();

        // foreach ($projects as $project) {
        //     $categories = Category::factory()
        //         ->count(rand(2,5))
        //         ->create([
        //             'project_id' => $project->id,
        //         ]);
        //     foreach ($categories as $category) {
        //         if (rand(0,1)%2===0) {
        //             Subcategory::factory()
        //                 ->count(rand(2,3))
        //                 ->create([
        //                     'category_id' => $category->id,
        //                 ])->each(function ($subcategory) use ($project, $category) {
        //                     $items = Item::factory()
        //                         ->count($subcategory->expected_items_count)
        //                         ->create([
        //                             'project_id' => $project->id,
        //                             'category_id' => $category->id,
        //                             'subcategory_id' => $subcategory->id,
        //                             'current_status_id' => Status::inRandomOrder()->first()->id,
        //                         ]);
        //                     $action = new GenerateItemQrCodeAction();
        //                     $items->each(fn (Item $item) => $action->handle($item));
        //                 });
        //         }
        //         $items = Item::factory()
        //             ->count($category->expected_items_count)
        //             ->create([
        //                 'project_id' => $project->id,
        //                 'category_id' => $category->id,
        //                 'current_status_id' => Status::inRandomOrder()->first()->id,
        //             ]);
        //         $action = new GenerateItemQrCodeAction();
        //         $items->each(fn (Item $item) => $action->handle($item));
        //     }
        // }

        // $this->call(ItemStatusSeeder::class);
    }
}
