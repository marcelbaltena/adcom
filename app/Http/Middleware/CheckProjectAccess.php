<?php
// ============================================
// CheckProjectAccess Middleware
// app/Http/Middleware/CheckProjectAccess.php
// ============================================

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckProjectAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @param  string  $permission
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, $permission = 'view')
    {
        $project = $request->route('project');
        
        if (!$project) {
            return $next($request);
        }
        
        $user = auth()->user();
        
        switch ($permission) {
            case 'view':
                if (!$user->canViewProject($project)) {
                    abort(403, 'Je hebt geen toegang tot dit project.');
                }
                break;
                
            case 'edit':
                if (!$user->canEditProject($project)) {
                    abort(403, 'Je hebt geen rechten om dit project te bewerken.');
                }
                break;
                
            case 'manage_team':
                if (!$user->can('manageTeam', $project)) {
                    abort(403, 'Je hebt geen rechten om het projectteam te beheren.');
                }
                break;
                
            case 'financials':
                if (!$user->can('viewFinancials', $project)) {
                    abort(403, 'Je hebt geen toegang tot de financiële gegevens van dit project.');
                }
                break;
        }
        
        return $next($request);
    }
}

// ============================================
// CheckRole Middleware
// app/Http/Middleware/CheckRole.php
// ============================================

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @param  string  ...$roles
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }
        
        $user = auth()->user();
        
        if (!in_array($user->role, $roles)) {
            abort(403, 'Je hebt niet de juiste rol om deze actie uit te voeren.');
        }
        
        return $next($request);
    }
}

// ============================================
// CheckPermission Middleware
// app/Http/Middleware/CheckPermission.php
// ============================================

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @param  string  $permission
     * @param  string|null  $resource
     * @param  string  $action
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, $permission, $resource = null, $action = 'view')
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }
        
        $user = auth()->user();
        
        if (!$user->hasPermission($permission, $resource, $action)) {
            abort(403, 'Je hebt geen toestemming om deze actie uit te voeren.');
        }
        
        return $next($request);
    }
}

// ============================================
// CheckCompanyAccess Middleware
// app/Http/Middleware/CheckCompanyAccess.php
// ============================================

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckCompanyAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }
        
        $user = auth()->user();
        $company = $request->route('company');
        
        if (!$company) {
            return $next($request);
        }
        
        // Admin can access all companies
        if ($user->role === 'admin') {
            return $next($request);
        }
        
        // Users can only access their own company
        if ($user->company_id !== $company->id) {
            abort(403, 'Je hebt geen toegang tot dit bedrijf.');
        }
        
        return $next($request);
    }
}

// ============================================
// CheckActiveUser Middleware
// app/Http/Middleware/CheckActiveUser.php
// ============================================

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckActiveUser
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if (auth()->check() && !auth()->user()->is_active) {
            Auth::logout();
            
            return redirect()->route('login')
                ->with('error', 'Je account is gedeactiveerd. Neem contact op met de beheerder.');
        }
        
        return $next($request);
    }
}

// ============================================
// LogActivity Middleware
// app/Http/Middleware/LogActivity.php
// ============================================

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\ActivityLog;

class LogActivity
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);
        
        // Only log for authenticated users and specific methods
        if (auth()->check() && in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            $this->logUserActivity($request, $response);
        }
        
        return $response;
    }
    
    /**
     * Log user activity
     */
    private function logUserActivity(Request $request, $response)
    {
        $user = auth()->user();
        $action = $this->getActionFromRequest($request);
        
        if (!$action) {
            return;
        }
        
        // Get the model if it's a resource route
        $model = $this->getModelFromRoute($request);
        
        if ($model) {
            ActivityLog::log($model, $action, $this->getDescription($request, $action));
        } else {
            // Log general activity
            ActivityLog::create([
                'user_id' => $user->id,
                'action' => $action,
                'description' => $this->getDescription($request, $action),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        }
    }
    
    /**
     * Get action from request
     */
    private function getActionFromRequest(Request $request): ?string
    {
        $method = $request->method();
        $routeName = $request->route()->getName();
        
        if (!$routeName) {
            return null;
        }
        
        // Map route patterns to actions
        if (str_contains($routeName, '.store') || $method === 'POST') {
            return 'created';
        } elseif (str_contains($routeName, '.update') || in_array($method, ['PUT', 'PATCH'])) {
            return 'updated';
        } elseif (str_contains($routeName, '.destroy') || $method === 'DELETE') {
            return 'deleted';
        }
        
        return null;
    }
    
    /**
     * Get model from route
     */
    private function getModelFromRoute(Request $request)
    {
        $route = $request->route();
        
        // Get all route parameters
        $parameters = $route->parameters();
        
        // Try to find a model in the parameters
        foreach ($parameters as $parameter) {
            if (is_object($parameter) && method_exists($parameter, 'getMorphClass')) {
                return $parameter;
            }
        }
        
        return null;
    }
    
    /**
     * Get description for activity
     */
    private function getDescription(Request $request, string $action): string
    {
        $routeName = $request->route()->getName();
        $segments = explode('.', $routeName);
        
        if (count($segments) >= 2) {
            $resource = $segments[0];
            return ucfirst($action) . ' ' . str_singular($resource);
        }
        
        return ucfirst($action) . ' resource';
    }
}