<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Folder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;


class AdminFolderController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'status' => ['nullable', Rule::in(Folder::STATUSES)],
        ]);

        $folders = Folder::with(['user', 'parent', 'license'])
            ->when($filters['status'] ?? null, fn($query, $status) => $query->where('status', $status))
            ->latest()
            ->paginate(10)
            ->withQueryString();

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
            'status' => ['required', Rule::in(Folder::STATUSES)],
        ]);

        DB::transaction(fn() => $folder->updateStatusRecursive($validated['status']));

        return redirect()
            ->route('admin.folder.index')
            ->with('success', 'Folder status updated successfully.');
    }
}
