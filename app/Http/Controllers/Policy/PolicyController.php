<?php

namespace App\Http\Controllers\Policy;

use Illuminate\Http\Request;
use App\Models\License;
use App\Http\Controllers\Controller;

class PolicyController extends Controller
{
    public function index()
    {
        $licenses = License::where('is_active', true)
            ->orderBy('name')
            ->get();

        $sellerPolicyVersion = config('rupaia.seller_policy_version');

        return view('policies.index', compact('licenses', 'sellerPolicyVersion'));
    }
}
