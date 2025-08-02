<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'email',
        'phone',
        'website',
        'address',
        'city',
        'postal_code',
        'country',
        'kvk_number',
        'vat_number',
        'iban',
        'currency',
        'timezone',
        'default_hourly_rate',
        'is_active',
        'settings'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'default_hourly_rate' => 'decimal:2',
        'settings' => 'array'
    ];

    protected $attributes = [
        'currency' => 'EUR',
        'timezone' => 'Europe/Amsterdam',
        'is_active' => true,
        'default_hourly_rate' => 75.00
    ];

    // Relationships
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function billingProjects(): HasMany
    {
        return $this->hasMany(Project::class, 'billing_company_id');
    }

    public function createdProjects(): HasMany
    {
        return $this->hasMany(Project::class, 'created_by_company_id');
    }


    // Accessors
    public function getFullAddressAttribute(): ?string
    {
        $parts = array_filter([
            $this->address,
            $this->city,
            $this->postal_code,
            $this->country
        ]);

        return !empty($parts) ? implode(', ', $parts) : null;
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    // Helper methods
    public function activate()
    {
        $this->update(['is_active' => true]);
    }

    public function deactivate()
    {
        $this->update(['is_active' => false]);
    }

    public function getTotalProjectValue(): float
    {
        return $this->billingProjects()->sum('project_value') ?? 0;
    }

    public function getActiveUsersCount(): int
    {
        return $this->users()->where('is_active', true)->count();
    }
	
	public function customers(): HasMany
{
    return $this->hasMany(Customer::class);
}

    public function canBeDeleted(): bool
    {
        $hasActiveUsers = $this->users()->where('is_active', true)->exists();
        $hasProjects = $this->billingProjects()->exists() || $this->createdProjects()->exists();
        
        return !$hasActiveUsers && !$hasProjects;
    }
}