<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Component extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'unit_of_measure',
        'current_quantity',
        'safety_stock_threshold',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'current_quantity' => 'decimal:2',
            'safety_stock_threshold' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function stockAlerts(): HasMany
    {
        return $this->hasMany(StockAlert::class);
    }

    // Derived status — NOT stored. NORMAL / CRITICAL / OUT_OF_STOCK.
    public function getStockStatusAttribute(): string
    {
        if ($this->current_quantity <= 0) {
            return 'OUT_OF_STOCK';
        }

        if ($this->current_quantity <= $this->safety_stock_threshold) {
            return 'CRITICAL';
        }

        return 'NORMAL';
    }

    public function scopeBelowSafetyStock(Builder $query): Builder
    {
        return $query->whereColumn('current_quantity', '<=', 'safety_stock_threshold');
    }
}
