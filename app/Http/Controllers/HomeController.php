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
            ->where('sale_status', 'available')
            ->with('user', 'tags', 'folder')
            ->latest()
            ->paginate(99);

        return view('welcome', compact('contents'));
    }

    public function showDetailContent(Content $content)
    {
        if ($content->visibility !== 'public' || $content->status !== 'active' || $content->sale_status !== 'available') {
            abort(404);
        }

        $content->load('user', 'tags', 'folder');

        // konten terkait berdasarkan folder yang sama
        $relatedContents = collect();

        if ($content->folder_id) {
            $relatedContents = Content::where('folder_id', $content->folder_id)
                ->where('id', '!=', $content->id)
                ->where('visibility', 'public')
                ->where('status', 'active')
                ->latest()
                ->take(12)
                ->get();
        }

        // konten berdasarkan tag yang sama
        $relatedByTags = collect();

        if ($content->tags->isNotEmpty()) {
            $tagIds = $content->tags->pluck('id'); //Ambil semua ID tag dari konten ini
            $relatedByTags = Content::whereHas('tags', function ($query) use ($tagIds) { // Cek relasi tags
                $query->whereIn('tags.id', $tagIds); // Cari konten yang memiliki tag yang sama
            })
                ->where('id', '!=', $content->id)
                ->where('visibility', 'public')
                ->where('status', 'active')
                ->latest()
                ->take(12)
                ->get();
        }

        // foreach ($content->tags as $tag) {
        //     $relatedByTags = $relatedByTags->merge(
        //         $tag->contents()
        //             ->where('id', '!=', $content->id)
        //             ->where('visibility', 'public')
        //             ->where('status', 'active')
        //             ->latest()
        //             ->take(4)
        //             ->get()
        //     );


        return view('dashboard.content.show-detail-content', compact('content', 'relatedContents', 'relatedByTags'));
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
            ->where('sale_status', 'available')
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

    public function showPublishedFolder(Folder $folder)
    {
        if ($folder->visibility !== 'public' || $folder->status !== 'active') {
            abort(404);
        }

        $folder->load('user', 'parent');

        $subfolders = $folder->children()
            ->where('visibility', 'public')
            ->where('status', 'active')
            ->latest()
            ->get();

        $contents = $folder->contents()
            ->where('visibility', 'public')
            ->where('status', 'active')
            ->where('sale_status', 'available')
            ->with('user', 'tags')
            ->latest()
            ->paginate(12);

        $hasPurchasableBundleContents = $folder->is_bundle
            ? $folder->hasPurchasableBundleContents()
            : false;

        $breadcrumbs = collect();
        $tempFolder = $folder;

        while ($tempFolder) {
            if ($tempFolder->visibility === 'public' && $tempFolder->status === 'active') {
                $breadcrumbs->prepend($tempFolder);
            }
            $tempFolder = $tempFolder->parent;
        }

        return view('dashboard.folder.show-published-folder', compact('folder', 'subfolders', 'contents', 'breadcrumbs', 'hasPurchasableBundleContents'));
    }
}
