<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomerGroup extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'discount_percentage',
        'is_default',
    ];

    protected $casts = [
        'discount_percentage' => 'decimal:2',
        'is_default' => 'boolean',
    ];

    public function scopeDefault(Builder $query): Builder
    {
        return $query->where('is_default', true);
    }

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }
}
