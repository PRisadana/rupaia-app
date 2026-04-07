<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Content;
use App\Models\ShowcaseItem;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;


class ShowcaseController extends Controller
{
    public function showcaseIndex(Request $request)
    {
        $user = $request->user();

        $showcaseItems = $user->showcaseItems()->with('content')->latest()->paginate(12);

        // Kirim data ke view
        return view('dashboard.showcase.index', compact('showcaseItems'));
    }

    public function showcaseCreate(Request $request)
    {
        $user = $request->user();

        return view('dashboard.showcase.create', compact('user'));
    }

    public function showcaseStore(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'custom_path' => 'required|image|mimes:jpg,jpeg,png|max:10240',
            // 'item_source' => 'required|in:custom,content',
            'description' => 'nullable|string|max:255',

        ]);

        $file = $request->file('custom_path');
        $fileName = time() . '_' . $file->getClientOriginalName();

        $manager = new ImageManager(new Driver());
        $image = $manager->read($file->getRealPath());

        $image->scale(width: 1080);

        $path = "showcase/{$user->id}/{$fileName}";
        Storage::disk('public')->put($path, (string) $image->encode());

        ShowcaseItem::create([
            'seller_id' => $user->id,
            'content_id' => null,
            'custom_path' => $path,
            'item_source' => 'custom',
            'description' => $validated['description'] ?? null,
            // 'status' => 'active',
        ]);

        return redirect()->route('showcase.index')->with('success', 'Showcase item created successfully!');
    }

    public function showcaseEdit(Request $request, ShowcaseItem $showcaseItem)
    {
        $this->authorize('update', $showcaseItem);

        return view('dashboard.showcase.edit', compact('showcaseItem'));
    }

    public function showcaseUpdate(Request $request, ShowcaseItem $showcaseItem)
    {
        $this->authorize('update', $showcaseItem);

        $validated = $request->validate([
            'description' => 'nullable|string|max:255',
        ]);

        $showcaseItem->update($validated);

        return redirect()->route('showcase.index')->with('success', 'Showcase item updated successfully!');
    }

    public function showcaseDestroy(ShowcaseItem $showcaseItem)
    {
        $this->authorize('delete', $showcaseItem);

        if ($showcaseItem->custom_path) {
            Storage::disk('public')->delete($showcaseItem->custom_path);
        }

        $showcaseItem->delete();

        return redirect()->route('showcase.index')->with('success', 'Showcase item deleted successfully!');
    }

    public function showcaseFromContentCreate(Request $request)
    {
        $user = $request->user();
        $contents = $user->contents()
            ->where('status', 'active')
            ->latest()
            ->paginate(12);

        return view('dashboard.showcase.create-from-content', compact('contents'));
    }

    public function showcaseFromContentStore(Request $request)
    {
        $user = $request->user();
        $validated = $request->validate([
            'content_id' => [
                'required',
                Rule::exists('contents', 'id')->where(function ($query) use ($user) {
                    return $query->where('seller_id', $user->id);
                }),
            ],
            'description' => 'nullable|string|max:255',
        ]);

        $content = Content::where('id', $validated['content_id'])
            ->where('seller_id', $user->id)
            ->firstOrFail();

        $alreadyExists = ShowcaseItem::where('seller_id', $user->id)
            ->where('content_id', $content->id)
            ->exists();

        if ($alreadyExists) {
            return back()
                ->withErrors([
                    'content_id' => 'This content has already been added to your showcase.'
                ])
                ->withInput();
        }

        ShowcaseItem::create([
            'seller_id' => $user->id,
            'content_id' => $content->id,
            'custom_path' => null,
            'item_source' => 'content',
            'description' => $user->contents()->where('id', $validated['content_id'])->value('content_description'),
            // 'status' => $validated['status'],
        ]);

        return redirect()
            ->route('showcase.index')
            ->with('success', 'Content successfully added to showcase.');
    }
}
