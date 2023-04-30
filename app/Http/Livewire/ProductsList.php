<?php

declare(strict_types=1);

namespace App\Http\Livewire;

use App\Exports\ProductsExport;
use App\Models\Category;
use App\Models\Country;
use App\Models\Product;
use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ProductsList extends Component
{
    use WithPagination;

    public array $categories = [];

    public array $countries = [];

    public string $sortColumn = 'products.name';

    public string $sortDirection = 'asc';

    public array $searchColumns = [
        'name'        => '',
        'price'       => ['', ''],
        'description' => '',
        'category_id' => 0,
        'country_id'  => 0,
    ];

    public array $selected = [];

    protected $queryString = [
        'sortColumn' => [
            'except' => 'products.name',
        ],
        'sortDirection' => [
            'except' => 'asc',
        ],
    ];

    protected $listeners = ['delete', 'deleteSelected'];

    public function sortByColumn(string $column): void
    {
        if ($this->sortColumn === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->reset('sortDirection');
            $this->sortColumn = $column;
        }
    }

    public function deleteConfirm(string $method, ?int $id = null): void
    {
        $this->dispatchBrowserEvent('swal:confirm', [
            'type'   => 'warning',
            'title'  => __('Are you sure?'),
            'text'   => '',
            'id'     => $id,
            'method' => $method,
        ]);
    }

    public function delete(int $id): void
    {
        $product = Product::findOrFail($id);

        if ($product->orders()->exists()) {
            $this->addError('orderexist', 'This product cannot be deleted, it already has orders');

            return;
        }

        $product->delete();
    }

    public function getSelectedCountProperty(): int
    {
        return count($this->selected);
    }

    public function deleteSelected(): void
    {
        $products = Product::with('orders')->whereIn('id', $this->selected)->get();

        foreach ($products as $product) {
            if ($product->orders()->exists()) {
                $this->addError('orderexist', "Product <span class='font-bold'>{$product->name}</span> cannot be deleted, it already has orders");

                return;
            }
        }

        $products->each->delete();

        $this->reset('selected');
    }

    public function export($format): BinaryFileResponse
    {
        abort_if(! in_array($format, ['csv', 'xlsx', 'pdf']), Response::HTTP_NOT_FOUND);

        return Excel::download(new ProductsExport($this->selected), 'products.' . $format);
    }

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
            ->join('countries', 'countries.id', '=', 'products.country_id')
            ->select(['products.*', 'countries.id as countryId', 'countries.name as countryName'])
            ->with('categories:id,name');

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
                ->when($column === 'country_id', fn ($products) =>
                    $products->whereRelation('country', 'id', $value)
                )
                ->when($column === 'name', fn ($products) =>
                    $products->where('products.' . $column, 'LIKE', '%' . $value . '%')
                );
        }

        $products->orderBy($this->sortColumn, $this->sortDirection);

        return $products->paginate(10);
    }
}
