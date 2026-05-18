<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Folder;
use Illuminate\Http\Request;

class AdminFolderController extends Controller
{
    public function index(Request $request)
    {
        $folders = Folder::with(['user', 'parent'])
            // ->when($request->filled('status'), function ($query) use ($request) {
            //     $query->where('status', $request->status);
            // })
            // ->when($request->filled('visibility'), function ($query) use ($request) {
            //     $query->where('visibility', $request->visibility);
            // })
            // ->when($request->filled('is_bundle'), function ($query) use ($request) {
            //     $query->where('is_bundle', $request->is_bundle);
            // })
            ->latest()
            ->paginate(10);
        // ->withQueryString();

        return view('admin.folder.index', compact('folders'));
    }

    public function editStatusFolder(Request $request, Folder $folder)
    {
        $folder->load(['user', 'parent', 'children', 'contents']);

        return view('admin.folder.edit', compact('folder'));
    }

    public function updateStatusFolder(Request $request, Folder $folder)
    {
        $validated = $request->validate([
            'status' => 'required|in:active,banned',
        ]);

        $folder->updateStatusRecursive($validated['status']);

        return redirect()
            ->route('admin.folder.index')
            ->with('success', 'Folder status updated successfully.');
    }
}
