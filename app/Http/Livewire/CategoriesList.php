<?php

declare(strict_types=1);

namespace App\Http\Livewire;

use App\Models\Category;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithPagination;

class CategoriesList extends Component
{
    use WithPagination;

    public Category $category;

    public Collection $categories;

    public bool $showModal = false;

    public array $activeCategories = [];

    public int $editedCategoryId = 0;

    protected $listeners = ['delete'];

    public function openModal(): void
    {
        $this->showModal = true;

        $this->category = new Category;
    }

    public function updatedCategoryName(): void
    {
        $this->category->slug = Str::slug($this->category->name);
    }

    public function toggleIsActive($categoryId): void
    {
        Category::where('id', $categoryId)->update([
            'is_active' => $this->activeCategories[$categoryId],
        ]);
    }

    public function updateOrder(array $list): void
    {
        foreach ($list as $item) {
            $category = $this->categories->firstWhere('id', $item['value']);

            if ($category['position'] === $item['order']) {
                continue;
            }

            Category::where('id', $item['value'])->update(['position' => $item['order']]);
        }
    }

    public function editCategory(int $categoryId): void
    {
        $this->editedCategoryId = $categoryId;

        $this->category = Category::find($categoryId);
    }

    public function cancelCategoryEdit(): void
    {
        $this->resetValidation();
        $this->reset('editedCategoryId');
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
        Category::findOrFail($id)->delete();
    }

    protected function rules(): array
    {
        return [
            'category.name' => ['required', 'string', 'min:3'],
            'category.slug' => ['nullable', 'string'],
        ];
    }

    public function save(): void
    {
        if ($this->editedCategoryId === 0) {
            $this->category->position = Category::max('position') + 1;
        }

        $this->category->save();

        $this->reset('showModal');
        $this->resetValidation();
        $this->reset('showModal', 'editedCategoryId');
    }

    public function render()
    {
        $categories = Category::orderBy('position')->paginate(10);

        $this->categories = collect($categories->items());

        $this->activeCategories = $this->categories->mapWithKeys(
            fn (Category $category) => [$category->id => (bool) $category->is_active]
        )->toArray();

        return view('livewire.categories-list', [
            'links' => $categories->links(),
        ]);
    }
}
