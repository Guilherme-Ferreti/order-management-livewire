<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'is_active',
        'position',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'position'  => 'integer',
    ];

    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', 1);
    }
}
