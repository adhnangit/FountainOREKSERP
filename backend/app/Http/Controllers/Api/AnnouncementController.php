<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\AnnouncementRead;
use App\Services\BranchContextService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AnnouncementController extends Controller
{
    public function __construct(private BranchContextService $branchContext) {}

    /** Company-wide bulletin — visible to every authenticated user, not permission-gated. */
    public function index(Request $request): JsonResponse
    {
        $userId = auth()->id();

        $q = Announcement::with('createdBy:id,name')
            ->withCount('reads')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->where(fn($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>=', now()->toDateString()));

        // "All Branches" mode sees every announcement, company-wide and branch-specific
        // alike (cumulative) — a specific branch sees only its own plus company-wide ones.
        // Note: branch_id can't use where('branch_id', $branchId) when $branchId might be
        // null, since Laravel compiles that to "= NULL" (always false) rather than IS NULL.
        if (!$this->branchContext->isAllBranches()) {
            $branchId = $this->branchContext->getBranchId();
            $q->where(fn($q) => $q->whereNull('branch_id')->orWhere('branch_id', $branchId));
        }

        if ($request->boolean('unread_only')) {
            $q->whereDoesNotHave('reads', fn($rq) => $rq->where('user_id', $userId));
        }

        $announcements = $q->orderByDesc('is_pinned')->orderByDesc('published_at')->get();

        $readIds = AnnouncementRead::where('user_id', $userId)
            ->whereIn('announcement_id', $announcements->pluck('id'))
            ->pluck('announcement_id')->all();
        $announcements->each(fn($a) => $a->is_read = in_array($a->id, $readIds, true));

        return response()->json($announcements);
    }

    public function markRead(Announcement $announcement): JsonResponse
    {
        AnnouncementRead::firstOrCreate(
            ['announcement_id' => $announcement->id, 'user_id' => auth()->id()],
            ['read_at' => now()]
        );
        return response()->json(['message' => 'Marked as read.']);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'branch_id' => 'nullable|exists:branches,id',
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'is_pinned' => 'nullable|boolean',
            'published_at' => 'nullable|date',
            'expires_at' => 'nullable|date',
        ]);
        $data['created_by'] = auth()->id();
        $data['published_at'] = $data['published_at'] ?? now();

        return response()->json(Announcement::create($data)->load('createdBy:id,name'), 201);
    }

    public function update(Request $request, Announcement $announcement): JsonResponse
    {
        $data = $request->validate([
            'branch_id' => 'nullable|exists:branches,id',
            'title' => 'sometimes|string|max:255',
            'body' => 'sometimes|string',
            'is_pinned' => 'nullable|boolean',
            'published_at' => 'nullable|date',
            'expires_at' => 'nullable|date',
        ]);

        $announcement->update($data);
        return response()->json($announcement->fresh('createdBy:id,name'));
    }

    public function destroy(Announcement $announcement): JsonResponse
    {
        $announcement->delete();
        return response()->json(['message' => 'Announcement deleted.']);
    }
}
