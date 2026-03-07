<?php

namespace App\Http\Controllers;

use App\Models\Content;
use App\Models\Folder;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        $contents = Content::where('visibility', 'public')
            ->where('status', 'active')
            ->with('user', 'tags', 'folder')
            ->latest()
            ->paginate(99);

        return view('welcome', compact('contents'));
    }

    public function showDetailContent(Content $content)
    {
        if ($content->visibility === 'private' || $content->status !== 'active') {
            abort(404);
        }

        $content->load('user', 'tags', 'folder');

        return view('dashboard.content.show-detail-content', compact('content'));
    }
}
