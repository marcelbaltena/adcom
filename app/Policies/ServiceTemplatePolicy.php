<?php

namespace App\Policies;

use App\Models\ServiceTemplate;
use App\Models\User;

class ServiceTemplatePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, ServiceTemplate $serviceTemplate): bool
    {
        return $user->company_id === $serviceTemplate->company_id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, ServiceTemplate $serviceTemplate): bool
    {
        return $user->company_id === $serviceTemplate->company_id;
    }

    public function delete(User $user, ServiceTemplate $serviceTemplate): bool
    {
        return $user->company_id === $serviceTemplate->company_id;
    }
}