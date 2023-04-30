<?php

declare(strict_types=1);

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'name'  => 'Test User',
            'email' => 'test@example.com',
        ]);

        $this->call([
            CountriesSeeder::class,
        ]);

        $categories = Category::factory(10)->create();

        Product::factory(100)
            ->create()
            ->each(fn (Product $product) =>
                $product->categories()->attach($categories->random(rand(1, 3)))
            );
    }
}
