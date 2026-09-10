<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductionRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'production_order_id',
        'produced_quantity',
        'recorded_by',
        'recorded_at',
        'shift',
    ];

    protected function casts(): array
    {
        return [
            'recorded_at' => 'datetime',
        ];
    }

    public function productionOrder(): BelongsTo
    {
        return $this->belongsTo(ProductionOrder::class);
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    // 1:N on purpose — supports reinspection as a new row on the same record.
    public function qualityInspections(): HasMany
    {
        return $this->hasMany(QualityInspection::class);
    }
}
