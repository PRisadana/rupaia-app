<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Content;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class AdminContentController extends Controller
{
    public function index()
    {
        $contents = Content::with([
            'user',
            'folder',
            'license',
            'similarContent.user',
        ])
            ->latest()
            ->paginate(10);

        return view('admin.content.index', compact('contents'));
    }

    public function editStatusContent(Request $request, Content $content)
    {
        $content->load([
            'user',
            'folder',
            'tags',
            'license',
            'similarContent.user',
            'similarContent.folder',
            'reviewer',
        ]);

        return view('admin.content.edit', compact('content'));
    }

    public function updateStatusContent(Request $request, Content $content)
    {
        $validated = $request->validate([
            'status' => [
                'required',
                Rule::in([
                    'pending_review',
                    'active',
                    'rejected',
                    'banned',
                ]),
            ],
            'review_note' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ]);

        $content->update([
            'status' => $validated['status'],
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
            'review_note' => $validated['review_note'] ?? null,
        ]);

        return redirect()
            ->route('admin.content.index')
            ->with('success', 'Content review status updated successfully.');
    }
}
