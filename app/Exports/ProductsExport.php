<?php

declare(strict_types=1);

namespace App\Exports;

use App\Models\Product;
use Illuminate\Support\Collection;
 
class ProductsExport implements FromCollection, WithHeadings, WithMapping
{
    public function __construct(private array $productIDs) {}
 
    public function headings(): array
    {
        return [
            'Name',
            'Categories',
            'Country',
            'Price'
        ];
    }
 
    public function map($product): array
    {
        return [
            $product->name,
            $product->categories->pluck('name')->implode(', '),
            $product->country->name,
            '$' . number_format($product->price, 2)
        ];
    }
 
    public function collection(): Collection
    {
        return Product::with('categories', 'country')->find($this->productIDs);
    }
}
