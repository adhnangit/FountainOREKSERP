<?php

namespace App\Http\Middleware;

use App\Services\BranchContextService;
use Closure;
use Illuminate\Http\Request;

class SetBranchContext
{
    public function __construct(private BranchContextService $branchContext) {}

    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        if (!$user) {
            return $next($request);
        }

        $headerBranch = $request->header('X-Branch-Id');
        $branchId = $headerBranch
            ?? $request->input('branch_id')
            ?? ($user->isSuperAdmin() ? null : $user->default_branch_id);

        if ($branchId && !$user->isSuperAdmin()) {
            if (!$user->canAccessBranch((int) $branchId)) {
                return response()->json(['message' => 'Unauthorized branch access.'], 403);
            }
        }

        // Super admin with no X-Branch-Id header → null (all-branch view)
        $this->branchContext->setBranchId($branchId ? (int) $branchId : null);
        $this->branchContext->setUser($user);

        return $next($request);
    }
}
