<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Content;
use App\Models\ShowcaseItem;
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

    public function showShowcase(User $user)
    {
        $showcaseItems = $user->showcaseItems()
            ->where('status', 'active')
            ->latest()
            ->get();

        return view('dashboard.content.show-author', compact('user', 'showcaseItems'));
    }

    public function showPublishedContent(User $user)
    {
        $contents = $user->contents()
            ->where('visibility', 'public')
            ->where('status', 'active')
            ->with('tags', 'folder')
            ->latest()
            ->paginate(99);

        return view('dashboard.content.show-author-published-content', compact('user', 'contents'));
    }

    public function showDetailShowcase(ShowcaseItem $showcaseItem)
    {
        if ($showcaseItem->status !== 'active') {
            abort(404);
        }

        $showcaseItem->load('user', 'content');

        return view('dashboard.showcase.show-detail-showcase', compact('showcaseItem'));
    }
}
