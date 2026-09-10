<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CorrectiveAction extends Model
{
    use HasFactory;

    public const STATUS_OPEN = 'OPEN';
    public const STATUS_IN_PROGRESS = 'IN_PROGRESS';
    public const STATUS_DONE = 'DONE';
    public const STATUS_VALIDATED = 'VALIDATED';

    protected $fillable = [
        'quality_defect_id',
        'root_cause',
        'action_description',
        'responsible_user_id',
        'status',
        'validated_by',
        'validated_at',
        'requires_reinspection',
    ];

    protected function casts(): array
    {
        return [
            'validated_at' => 'datetime',
            'requires_reinspection' => 'boolean',
        ];
    }

    public function qualityDefect(): BelongsTo
    {
        return $this->belongsTo(QualityDefect::class);
    }

    public function responsibleUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsible_user_id');
    }

    public function validatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by');
    }
}
