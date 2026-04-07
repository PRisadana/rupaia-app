<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Content;
use Illuminate\Http\Request;

// class AuthorController extends Controller
// {
//     public function show(User $user)
//     {
//         $showcaseItems = $user->showcaseItems()
//             ->where('status', 'active')
//             ->latest()
//             ->get();

//         return view('dashboard.content.show-author', compact('user', 'showcaseItems'));
//     }

//     public function showPublishedContent(User $user)
//     {
//         $contents = $user->contents()
//             ->where('visibility', 'public')
//             ->where('status', 'active')
//             ->with('tags', 'folder')
//             ->latest()
//             ->paginate(99);

//         return view('dashboard.content.show-author-published-content', compact('user', 'contents'));
//     }
// }
