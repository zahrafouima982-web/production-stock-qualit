<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QualityInspection extends Model
{
    use HasFactory;

    public const RESULT_PENDING = 'PENDING';
    public const RESULT_PASS = 'PASS';
    public const RESULT_FAIL = 'FAIL';

    protected $fillable = [
        'production_record_id',
        'inspector_id',
        'result',
        'inspected_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'inspected_at' => 'datetime',
        ];
    }

    public function productionRecord(): BelongsTo
    {
        return $this->belongsTo(ProductionRecord::class);
    }

    public function inspector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'inspector_id');
    }

    public function inspectionItems(): HasMany
    {
        return $this->hasMany(InspectionItem::class);
    }

    public function qualityDefects(): HasMany
    {
        return $this->hasMany(QualityDefect::class);
    }
}
