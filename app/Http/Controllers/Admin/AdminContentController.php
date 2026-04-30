<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Content;
use Illuminate\Http\Request;

class AdminContentController extends Controller
{
    public function index()
    {
        $contents = Content::latest()->paginate(10);

        return view('admin.content.index', compact('contents'));
    }

    public function editStatusContent(Request $request, Content $content)
    {
        $content->load(['user', 'folder', 'tags']);

        return view('admin.content.edit', compact('content'));
    }

    public function updateStatusContent(Request $request, Content $content)
    {
        $validated = $request->validate([
            'status' => 'required|in:active,banned',
        ]);

        $content->update([
            'status' => $validated['status'],
        ]);

        return redirect()->route('admin.content.index')->with('success', 'Content status updated successfully.');
    }
}
