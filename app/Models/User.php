<?php

namespace App\Models;

// Note: this file replaces app/Models/User.php from Phase 1.
// Additions in Phase 2 are marked below — everything else is unchanged.

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    // Reverse relationships — actions this user has performed across modules.
    public function createdProductionOrders(): HasMany
    {
        return $this->hasMany(ProductionOrder::class, 'created_by');
    }

    public function productionRecords(): HasMany
    {
        return $this->hasMany(ProductionRecord::class, 'recorded_by');
    }

    public function inspectionsPerformed(): HasMany
    {
        return $this->hasMany(QualityInspection::class, 'inspector_id');
    }

    public function defectsDetected(): HasMany
    {
        return $this->hasMany(QualityDefect::class, 'detected_by');
    }

    public function correctiveActionsResponsible(): HasMany
    {
        return $this->hasMany(CorrectiveAction::class, 'responsible_user_id');
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class, 'performed_by');
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    public function hasRole(string $roleName): bool
    {
        return $this->role?->name === $roleName;
    }

    // ---- Phase 2 additions -------------------------------------------------

    public function isAdmin(): bool
    {
        return $this->hasRole(Role::ADMIN);
    }

    public function isProductionManager(): bool
    {
        return $this->hasRole(Role::PRODUCTION_MANAGER);
    }

    public function isQualityController(): bool
    {
        return $this->hasRole(Role::QUALITY_CONTROLLER);
    }

    public function isStockManager(): bool
    {
        return $this->hasRole(Role::STOCK_MANAGER);
    }

    /**
     * Named route this user should land on immediately after login.
     */
    public function dashboardRouteName(): string
    {
        return match ($this->role?->name) {
            Role::ADMIN => 'dashboard',
            Role::PRODUCTION_MANAGER => 'production.dashboard',
            Role::QUALITY_CONTROLLER => 'quality.dashboard',
            Role::STOCK_MANAGER => 'stock.dashboard',
            default => 'login',
        };
    }
}
