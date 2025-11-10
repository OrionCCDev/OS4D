<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDeleteRequest;
use App\Models\DeleteRequest;
use App\Services\DeleteRequestService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DeleteRequestController extends Controller
{
    private array $allowedTypes = [
        'user',
        'project',
        'project_folder',
        'project_file',
        'contractor',
        'project_manager',
        'external_stakeholder',
        'email_template',
        'task',
        'queue_job',
    ];

    public function __construct(private DeleteRequestService $service)
    {
    }

    public function store(StoreDeleteRequest $request): RedirectResponse
    {
        $data = $request->validated();

        if (!in_array($data['target_type'], $this->allowedTypes, true)) {
            return redirect()->back()->with('error', 'Unsupported delete request type.');
        }

        $input = [
            'requester_id' => $request->user()->id,
            'target_type' => $data['target_type'],
            'target_id' => $data['target_id'],
            'target_label' => $data['target_label'] ?? null,
            'reason' => $data['reason'] ?? null,
            'metadata' => $data['metadata'] ?? null,
        ];

        if (empty($input['target_label'])) {
            $tempRequest = new DeleteRequest($input);
            $target = $this->service->resolveTarget($tempRequest);
            if ($target) {
                $input['target_label'] = match ($input['target_type']) {
                    'user' => $target->name,
                    'project' => $target->name,
                    'project_folder' => $target->name,
                    'project_file' => $target->display_name ?? $target->original_name,
                    'contractor' => $target->name,
                    'project_manager' => $target->name,
                    'external_stakeholder' => $target->name,
                    'email_template' => $target->name,
                    'task' => $target->title,
                    default => $input['target_label'],
                };
            }
        }

        DeleteRequest::create($input);

        return redirect($data['redirect_url'] ?? url()->previous())
            ->with('status', 'Delete request submitted. An administrator will review it soon.');
    }

    public function index(Request $request): View
    {
        $this->authorizeAdmin();

        $status = $request->get('status');

        $query = DeleteRequest::with(['requester', 'reviewer'])->latest();
        if ($status && in_array($status, ['pending', 'approved', 'rejected'])) {
            $query->where('status', $status);
        }

        $requests = $query->paginate(25)->withQueryString();

        return view('admin.delete-requests.index', compact('requests', 'status'));
    }

    public function show(DeleteRequest $deleteRequest): View
    {
        $this->authorizeAdmin();

        $effectSummary = $this->service->buildEffectSummary($deleteRequest);
        $target = $this->service->resolveTarget($deleteRequest);

        return view('admin.delete-requests.show', compact('deleteRequest', 'effectSummary', 'target'));
    }

    public function approve(DeleteRequest $deleteRequest, Request $request): RedirectResponse
    {
        $this->authorizeAdmin();

        if (!$deleteRequest->isPending()) {
            return redirect()->back()->with('error', 'This request has already been processed.');
        }

        try {
            $this->service->execute($deleteRequest);

            $deleteRequest->update([
                'status' => 'approved',
                'reviewed_by' => Auth::id(),
                'reviewed_at' => now(),
                'review_notes' => $request->input('review_notes'),
            ]);

            return redirect()->route('admin.delete-requests.index')
                ->with('status', 'Delete request approved and item removed.');
        } catch (\Throwable $e) {
            report($e);
            return redirect()->back()->with('error', 'Failed to delete item: ' . $e->getMessage());
        }
    }

    public function reject(DeleteRequest $deleteRequest, Request $request): RedirectResponse
    {
        $this->authorizeAdmin();

        if (!$deleteRequest->isPending()) {
            return redirect()->back()->with('error', 'This request has already been processed.');
        }

        $deleteRequest->update([
            'status' => 'rejected',
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
            'review_notes' => $request->input('review_notes'),
        ]);

        return redirect()->route('admin.delete-requests.index')
            ->with('status', 'Delete request rejected.');
    }

    public function popup(): View
    {
        $this->authorizeAdmin();

        $requests = DeleteRequest::with('requester')
            ->pending()
            ->latest()
            ->take(10)
            ->get();

        return view('admin.delete-requests.popup', compact('requests'));
    }

    private function authorizeAdmin(): void
    {
        if (!Auth::user() || Auth::user()->role !== 'admin') {
            abort(403, 'Access denied. Admins only.');
        }
    }
}

