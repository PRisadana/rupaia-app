<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ShowcaseItem;

class AdminShowcaseController extends Controller
{
    public function index()
    {
        $showcaseItems = ShowcaseItem::latest()->paginate(10);

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
            'status' => 'required|in:active,banned',
        ]);

        $showcaseItem->update([
            'status' => $validated['status'],
        ]);

        return redirect()->route('admin.showcase.index')->with('success', 'Showcase item status updated successfully.');
    }
}
