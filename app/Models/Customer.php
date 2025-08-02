<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'name',
        'description',
        'type',
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
        'contact_person',
        'contact_email',
        'contact_phone',
        'currency',
        'default_hourly_rate',
        'payment_terms',
        'is_active',
        'billing_settings',
        'preferences',
        'notes',
        'industry',
        'size'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'default_hourly_rate' => 'decimal:2',
        'billing_settings' => 'array',
        'preferences' => 'array'
    ];

    protected $attributes = [
        'type' => 'company',
        'currency' => 'EUR',
        'payment_terms' => '30',
        'is_active' => true
    ];

    // Relationships
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    // Accessors
    public function getFullAddressAttribute(): ?string
    {
        $parts = array_filter([
            $this->address,
            $this->postal_code . ' ' . $this->city,
            $this->country
        ]);

        return !empty($parts) ? implode("\n", $parts) : null;
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->name;
    }

    public function getPrimaryContactAttribute(): ?string
    {
        if ($this->contact_person) {
            $contact = $this->contact_person;
            if ($this->contact_email) {
                $contact .= ' (' . $this->contact_email . ')';
            }
            return $contact;
        }
        
        return $this->contact_email ?: $this->email;
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

    public function scopeByCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeCompanies($query)
    {
        return $query->where('type', 'company');
    }

    public function scopeIndividuals($query)
    {
        return $query->where('type', 'individual');
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
        return $this->projects()->sum('project_value') ?? 0;
    }

    public function getActiveProjectsCount(): int
    {
        return $this->projects()->whereIn('status', ['active', 'in_progress'])->count();
    }

    public function getCompletedProjectsCount(): int
    {
        return $this->projects()->where('status', 'completed')->count();
    }

    public function canBeDeleted(): bool
    {
        return !$this->projects()->exists();
    }

    public function isCompany(): bool
    {
        return $this->type === 'company';
    }

    public function isIndividual(): bool
    {
        return $this->type === 'individual';
    }

    // Business methods
    public function createProject(array $projectData): Project
    {
        return $this->projects()->create(array_merge($projectData, [
            'customer_id' => $this->id,
            'billing_company_id' => $this->company_id,
            'currency' => $this->currency
        ]));
    }
}