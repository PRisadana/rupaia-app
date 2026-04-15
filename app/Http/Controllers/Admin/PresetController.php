<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Preset;

class PresetController extends Controller
{
    public function index()
    {
        $presets = Preset::latest()->paginate(10);

        return view('admin.preset.index', compact('presets'));
    }

    public function createPreset()
    {
        return view('admin.preset.create');
    }

    public function storePreset(Request $request)
    {
        $validated = $request->validate([
            'preset_name' => 'required|string|max:255',
            'preset_file_path' => 'required|file',
            'is_active' => 'required|boolean',
        ]);

        $file = $request->file('preset_file_path');
        if ($file->getClientOriginalExtension() !== 'cube') {
            return back()
                ->withErrors(['preset_file_path' => 'File must be a .cube file'])
                ->withInput();
        }

        $fileName = time() . '_' . $file->getClientOriginalName();
        $path = $file->storeAs('presets', $fileName, 'public');

        Preset::create([
            'preset_name' => $validated['preset_name'],
            'preset_file_path' => $path,
            'is_active' => $validated['is_active'],
        ]);

        return redirect()->route('admin.preset.index')->with('success', 'Preset created successfully.');
    }

    public function editPreset(Preset $preset)
    {
        return view('admin.preset.edit', compact('preset'));
    }

    public function updatePreset(Request $request, Preset $preset)
    {
        $validated = $request->validate([
            'preset_name' => 'required|string|max:255',
            // 'preset_file_path' => 'required|file|mimes:.cube',
            'is_active' => 'required|boolean',
        ]);

        $data = [
            'preset_name' => $validated['preset_name'],
            'is_active' => $validated['is_active'],
        ];

        // if ($request->hasFile('preset_file_path')) {
        //     if ($preset->preset_file_path) {
        //         Storage::disk('public')->delete($preset->preset_file_path);
        //     }

        //     $file = $request->file('preset_file_path');
        //     $fileName = time() . '_' . $file->getClientOriginalName();
        //     $path = $file->storeAs('presets', $fileName, 'public');

        //     $data['preset_file_path'] = $path;
        // }

        $preset->update($data);

        return redirect()->route('admin.preset.index')->with('success', 'Preset updated successfully.');
    }

    public function destroyPreset(Preset $preset)
    {
        if ($preset->preset_file_path) {
            Storage::disk('public')->delete($preset->preset_file_path);
        }

        $preset->delete();

        return redirect()->route('admin.preset.index')->with('success', 'Preset deleted successfully.');
    }
}
