<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ShowcaseItem;
use Illuminate\Validation\Rule;

class AdminShowcaseController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'status' => ['nullable', Rule::in(ShowcaseItem::STATUSES)],
        ]);

        $showcaseItems = ShowcaseItem::with(['user', 'content'])
            ->when($filters['status'] ?? null, fn($query, $status) => $query->where('status', $status))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.showcase.index', compact('showcaseItems'));
    }

    public function editStatusShowcase(Request $request, ShowcaseItem $showcaseItem)
    {
        $showcaseItem->load(['user', 'content']);

        return view('admin.showcase.edit', compact('showcaseItem'));
    }

    public function updateStatusShowcase(Request $request, ShowcaseItem $showcaseItem)
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(ShowcaseItem::STATUSES)],
        ]);

        $showcaseItem->update([
            'status' => $validated['status'],
        ]);

        return redirect()->route('admin.showcase.index')->with('success', 'Showcase item status updated successfully.');
    }
}
