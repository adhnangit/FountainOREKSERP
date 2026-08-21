<?php

namespace App\Services;

use App\Models\User;

class BranchContextService
{
    private ?int $branchId = null;
    private ?User $user = null;

    public function setBranchId(?int $branchId): void
    {
        $this->branchId = $branchId;
    }

    public function getBranchId(): ?int
    {
        return $this->branchId;
    }

    public function setUser(User $user): void
    {
        $this->user = $user;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function isAllBranches(): bool
    {
        return $this->branchId === null && ($this->user?->isSuperAdmin() ?? false);
    }

    public function applyScope($query, string $column = 'branch_id')
    {
        if ($this->isAllBranches()) {
            return $query;
        }
        // Anything that isn't "all branches" mode must always be filtered —
        // previously, a null branchId (only possible for a non-super-admin
        // with no resolvable default_branch_id) skipped the filter entirely,
        // silently leaking every branch's data instead of failing closed.
        $query->where($column, $this->branchId ?? -1);
        return $query;
    }
}
