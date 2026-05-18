<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Content;
use App\Models\Folder;
use App\Models\ShowcaseItem;
use App\Models\User;
use App\Models\Preset;
use App\Models\Tags;
use App\Models\Report;

use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $totalUsers = User::count();
        $totalContents = Content::count();
        $totalShowcaseItems = ShowcaseItem::count();
        $totalPresets = Preset::count();
        $totalTags = Tags::count();
        $totalFolders = Folder::count();
        $totalReports = Report::count();

        return view('admin.dashboard', compact('totalUsers', 'totalContents', 'totalShowcaseItems', 'totalPresets', 'totalTags', 'totalFolders', 'totalReports'));
    }
}
