<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class ServiceTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'name',
        'description',
        'category',
        'service_type',
        'base_price',
        'hourly_rate',
        'estimated_hours',
        'is_active',
        'is_popular',
        'tags',
        'order'
    ];

    protected $casts = [
        'base_price' => 'decimal:2',
        'hourly_rate' => 'decimal:2',
        'estimated_hours' => 'decimal:2',
        'is_active' => 'boolean',
        'is_popular' => 'boolean',
        'tags' => 'array',
        'order' => 'integer'
    ];

    protected $attributes = [
        'service_type' => 'fixed',
        'is_active' => true,
        'is_popular' => false,
        'order' => 0
    ];

    // Relationships
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class)->withDefault();
    }

    public function milestoneTemplates(): HasMany
    {
        return $this->hasMany(MilestoneTemplate::class)->orderBy('order');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopePopular($query)
    {
        return $query->where('is_popular', true);
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    // Calculate total price
    public function calculateTotalPrice(): float
    {
        if ($this->service_type === 'hourly') {
            return ($this->hourly_rate ?? 0) * ($this->estimated_hours ?? 0);
        }
        
        // For fixed/package, calculate from milestones
        return $this->milestoneTemplates->sum('price');
    }

    // Get total estimated hours
    public function getTotalEstimatedHours(): float
    {
        if ($this->service_type === 'hourly') {
            return $this->estimated_hours ?? 0;
        }

        return $this->milestoneTemplates->sum('estimated_hours');
    }

    // Clone entire service to project
    public function cloneToProject(Project $project, array $options = []): array
    {
        $createdMilestones = [];
        
        DB::beginTransaction();
        try {
            foreach ($this->milestoneTemplates as $index => $milestoneTemplate) {
                $milestoneOptions = array_merge($options, [
                    'order_offset' => $index
                ]);
                
                $milestone = $milestoneTemplate->cloneToProject($project, $milestoneOptions);
                $createdMilestones[] = $milestone;
            }
            
            // Recalculate project budget
            $project->recalculateBudget();
            
            DB::commit();
            return $createdMilestones;
            
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    // Get categories
    public static function getCategories(): array
    {
        return self::distinct('category')
            ->whereNotNull('category')
            ->pluck('category')
            ->toArray();
    }

    // Service type options
    public static function getServiceTypeOptions(): array
    {
        return [
            'hourly' => 'Uurtarief',
            'fixed' => 'Vaste prijs',
            'package' => 'Pakket'
        ];
    }
}