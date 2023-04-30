<?php

namespace App\Http\Livewire;

use App\Models\Category;
use App\Models\Country;
use App\Models\Product;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithPagination;

class ProductsList extends Component
{
    use WithPagination;

    public array $categories = [];

    public array $countries = [];

    public array $searchColumns = [ 
        'name'        => '',
        'price'       => ['', ''],
        'description' => '',
        'category_id' => 0,
        'country_id'  => 0,
    ]; 
 
    public function mount(): void
    {
        $this->categories = Category::pluck('name', 'id')->toArray();
        $this->countries  = Country::pluck('name', 'id')->toArray();
    }

    public function render(): View
    {
        return view('livewire.products-list', [
            'products' => $this->getProducts(),
        ]);
    }

    private function getProducts(): LengthAwarePaginator
    {
        $products = Product::query()
            ->with('categories:id,name', 'country:id,name');
 
        foreach ($this->searchColumns as $column => $value) {
            if (empty($value)) {
                continue;
            }

            $products->when($column === 'price', function ($products) use ($value) {
                if (is_numeric($value[0])) {
                    $products->where('products.price', '>=', $value[0] * 100);
                }

                if (is_numeric($value[1])) {
                    $products->where('products.price', '<=', $value[1] * 100);
                }
            })
            ->when($column === 'category_id', fn ($products) =>
                $products->whereRelation('categories', 'id', $value)
            )
            ->when($column === 'country_id',fn ($products) =>
                $products->whereRelation('country', 'id', $value)
            )
            ->when($column === 'name', fn ($products) =>
                $products->where('products.' . $column, 'LIKE', '%' . $value . '%')
            );
        }

        return $products->paginate(10);
    }
}
