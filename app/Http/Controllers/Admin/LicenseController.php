<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\License;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LicenseController extends Controller
{
    public function index()
    {
        $licenses = License::latest()->paginate(10);
        return view('admin.license.index', compact('licenses'));
    }

    public function create()
    {
        return view('admin.license.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => [
                'required',
                'string',
                'lowercase',
                'max:100',
                'alpha_dash',
                'unique:licenses,code',
            ],
            'description' => ['nullable', 'string', 'max:1000'],
            'terms' => ['nullable', 'string'],
            'is_active' => ['required', 'boolean'],
        ]);

        License::create($validated);

        return redirect()
            ->route('admin.license.index')
            ->with('success', 'License created successfully.');
    }

    public function edit(License $license)
    {
        return view('admin.license.edit', compact('license'));
    }

    public function update(Request $request, License $license)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => [
                'required',
                'string',
                'lowercase',
                'max:100',
                'alpha_dash',
                Rule::unique('licenses', 'code')->ignore($license->id),
            ],
            'description' => ['nullable', 'string', 'max:1000'],
            'terms' => ['nullable', 'string'],
            'is_active' => ['required', 'boolean'],
        ]);

        $license->update($validated);

        return redirect()
            ->route('admin.license.index')
            ->with('success', 'License updated successfully.');
    }

    public function destroy(License $license)
    {
        $usedByContents = $license->contents()->count();
        $usedByFolders = $license->folders()->count();

        if ($usedByContents > 0 || $usedByFolders > 0) {
            return back()->withErrors([
                'license' => "This license cannot be deleted because it is used by {$usedByContents} content(s) and {$usedByFolders} folder(s).",
            ]);
        }

        $license->delete();

        return redirect()
            ->route('admin.license.index')
            ->with('success', 'License deleted successfully.');
    }
}
