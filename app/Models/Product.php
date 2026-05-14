<?php

namespace App\Models;


use App\Models\User;
use App\Models\Unit;
use App\Models\Brand;
use App\Models\Category;
use App\Models\ProductVariant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'brand_id',
        'category_id',
        'unit_id',
        'type',
        'description',
        'is_active',
        'sku_prefix',
        'low_stock_threshold',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'low_stock_threshold' => 'integer',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeConfigurable(Builder $query): Builder
    {
        return $query->where('type', 'configurable');
    }

    public function scopeSimple(Builder $query): Builder
    {
        return $query->where('type', 'simple');
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class, 'product_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
