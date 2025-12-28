<?php

namespace App\Http\Controllers;

use App\Models\Content;
use App\Models\Folder;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        $contents = Content::where('visibility_content', 'public')
            ->with('user', 'tags', 'folder')
            ->latest()
            ->paginate(99);

        return view('welcome', compact('contents'));
    }
}
