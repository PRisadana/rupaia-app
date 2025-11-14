<?php

namespace App\Http\Controllers;

use App\Models\Content;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        $contents = Content::where('visibility_content', 'public')
            ->with('user', 'tags')
            ->latest()
            ->paginate(12);

        return view('welcome', compact('contents'));
    }
}
