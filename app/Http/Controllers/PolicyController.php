<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\License;

class PolicyController extends Controller
{
    public function index()
    {
        $licenses = License::where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('policies.index', compact('licenses'));
    }
}
