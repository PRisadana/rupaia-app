<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Content;
use App\Models\Preset;
use App\Services\EditingService;

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

    public function imagePreview(Request $request, Content $content, EditingService $editingService)
    {
        if (
            $content->visibility !== 'public'
            ||
            $content->status !== 'active'
        ) {
            abort(404);
        }

        $request->validate([
            'preset_id' => [
                'required',
                'integer',
                'exists:presets,id',
            ],
        ]);

        $preset = Preset::query()
            ->whereKey($request->preset_id)
            ->where('is_active', true)
            ->firstOrFail();

        $image = $editingService->generatePreview($content, $preset);

        return response(
            $image,
            200,
            [
                'Content-Type' => 'image/jpeg',
                'Cache-Control'
                => 'no-store, no-cache, must-revalidate, max-age=0',
            ]
        );
    }
}
