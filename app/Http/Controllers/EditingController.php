<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Content;
use App\Models\Preset;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class EditingController extends Controller
{
    public function showPreviewPage(Request $request, Content $content)
    {
        if ($content->visibility !== 'public' || $content->status !== 'active') {
            abort(404);
        }

        $presets = Preset::where('is_active', true)
            ->orderBy('preset_name')
            ->get();

        $selectedPresetId = $request->query('preset_id');

        if ($selectedPresetId) {
            $presetExists = Preset::where('id', $selectedPresetId)
                ->where('is_active', true)
                ->exists();

            if (! $presetExists) {
                return redirect()
                    ->route('editing.preview', $content->id)
                    ->withErrors(['preset_id' => 'Selected preset is not available.']);
            }
        }

        return view('dashboard.content.edit-preview', compact(
            'content',
            'presets',
            'selectedPresetId'
        ));
    }

    public function imagePreview(Request $request, Content $content)
    {
        if ($content->visibility !== 'public' || $content->status !== 'active') {
            abort(404);
        }

        $request->validate([
            'preset_id' => 'required|exists:presets,id',
        ]);

        $preset = Preset::where('id', $request->preset_id)
            ->where('is_active', true)
            ->firstOrFail();

        $imagePath = storage_path('app/public/' . $content->path_low_res);

        if (! file_exists($imagePath)) {
            abort(404, 'Preview image not found.');
        }

        $lutPath = storage_path('app/public/' . $preset->preset_file_path);

        if (! file_exists($lutPath)) {
            abort(404, 'Preset file not found.');
        }

        // copy lut file to editing service directory
        $pythonLutDir = base_path('editing-service/luts');
        if (! is_dir($pythonLutDir)) {
            mkdir($pythonLutDir, 0777, true);
        }

        $lutFilename = basename($lutPath);
        copy($lutPath, $pythonLutDir . DIRECTORY_SEPARATOR . $lutFilename);

        $response = Http::attach(
            'image',
            file_get_contents($imagePath),
            basename($imagePath)
        )->post('http://127.0.0.1:5001/apply-lut-preview', [
            'lut_filename' => $lutFilename,
        ]);

        if (! $response->successful()) {
            abort(500, 'Failed to generate preview. Response: ' . $response->body());
        }

        return response($response->body(), 200, [
            'Content-Type' => 'image/jpeg',
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
        ]);
    }
}
