<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tags;

class TagController extends Controller
{
    public function index(Request $request)
    {
        $tags = Tags::latest()->paginate(10);

        return view('admin.tag.index', compact('tags'));
    }

    public function createTag()
    {
        return view('admin.tag.create');
    }

    public function storeTag(Request $request)
    {
        $validated = $request->validate([
            'tag_name' => 'required|string|lowercase|max:255',
        ]);

        Tags::create($validated);

        return redirect()->route('admin.tag.index')->with('success', 'Tag created successfully.');
    }

    public function editTag(Tags $tag)
    {
        return view('admin.tag.edit', compact('tag'));
    }

    public function updateTag(Request $request, Tags $tag)
    {
        $validated = $request->validate([
            'tag_name' => 'required|string|lowercase|max:255',
        ]);

        $tag->update($validated);

        return redirect()->route('admin.tag.index')->with('success', 'Tag updated successfully.');
    }

    public function destroyTag(Tags $tag)
    {
        $tag->delete();

        return redirect()->route('admin.tag.index')->with('success', 'Tag deleted successfully.');
    }
}
