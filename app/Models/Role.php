<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    use HasFactory;

    public const ADMIN = 'ADMIN';
    public const PRODUCTION_MANAGER = 'PRODUCTION_MANAGER';
    public const QUALITY_CONTROLLER = 'QUALITY_CONTROLLER';
    public const STOCK_MANAGER = 'STOCK_MANAGER';

    protected $fillable = [
        'name',
        'description',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
