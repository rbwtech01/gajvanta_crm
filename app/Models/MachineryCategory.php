<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MachineryCategory extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (MachineryCategory $category) {
            $category->slug = Str::slug($category->name);
        });
    }

    public function items(): HasMany
    {
        return $this->hasMany(
            MachineryItem::class,
            'machinery_category_id'
        );
    }
}