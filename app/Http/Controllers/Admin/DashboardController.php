<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminAuditLog;
use App\Models\Board;
use App\Models\Task;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'role' => ['nullable', Rule::in(User::globalRoles())],
            'status' => ['nullable', Rule::in(['active', 'inactive'])],
        ]);

        $search = trim((string) ($filters['q'] ?? ''));
        $role = $filters['role'] ?? null;
        $status = $filters['status'] ?? null;

        $users = User::query()
            ->withCount(['teams as owned_teams_count', 'joinedTeams'])
            ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search): void {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            }))
            ->when($role, fn ($query, string $selectedRole) => $query->where('global_role', $selectedRole))
            ->when($status, fn ($query, string $selectedStatus) => $query->where('is_active', $selectedStatus === 'active'))
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return view('admin.dashboard', [
            'users' => $users,
            'filters' => ['q' => $search, 'role' => $role, 'status' => $status],
            'stats' => [
                'users' => User::query()->count(),
                'active_users' => User::query()->where('is_active', true)->count(),
                'superadmins' => User::query()->where('global_role', User::RoleSuperAdmin)->where('is_active', true)->count(),
                'teams' => Team::query()->count(),
                'projects' => Board::query()->count(),
                'tasks' => Task::query()->count(),
            ],
            'recentAudits' => AdminAuditLog::query()
                ->with(['actor:id,name', 'targetUser:id,name,email'])
                ->latest()
                ->limit(15)
                ->get(),
        ]);
    }
}
