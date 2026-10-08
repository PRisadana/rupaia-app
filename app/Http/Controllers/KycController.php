<?php

namespace App\Http\Controllers;

use App\Models\KycSubmission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class KycController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $latestSubmission = $user->kycSubmissions()
            ->latest('id')
            ->first();

        $alreadySeller = $user->role === 'seller';

        return view('kyc.index', compact('latestSubmission', 'alreadySeller'));
    }

    public function create(Request $request)
    {
        $user = $request->user();

        if ($user->role !== 'buyer') {
            return redirect()
                ->route('kyc.index')
                ->with('info', 'Your account already has seller access.');
        }

        $latestSubmission = $user->kycSubmissions()
            ->latest('id')
            ->first();

        if ($latestSubmission?->status === 'pending') {
            return redirect()
                ->route('kyc.index')
                ->with('info', 'Your KYC submission is still under review.');
        }

        if ($latestSubmission?->status === 'verified') {
            return redirect()
                ->route('kyc.index')
                ->with('info', 'Your KYC has already been verified.');
        }

        return view('kyc.create', compact('latestSubmission'));
    }

    public function store(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'full_name' => [
                'required',
                'string',
                'max:255',
            ],

            'id_number' => [
                'required',
                'digits:16', // karena NIK 16 digit
            ],

            'id_card' => [
                'required',
                'image',
                'mimes:jpg,jpeg,png',
                'max:2000', // 2 MB
            ],

            'data_truth_confirmation' => [
                'required',
                'accepted',
            ],

            'terms_accepted' => [
                'required',
                'accepted',
            ],
        ], [
            'full_name.required' => 'Full name is required.',

            'id_number.required' => 'NIK is required.',
            'id_number.digits' => 'NIK must consist of 16 digits.',

            'id_card.required' => 'Identity card image is required.',
            'id_card.image' => 'Identity card must be an image.',
            'id_card.mimes' => 'Identity card must be JPG, JPEG, or PNG.',
            'id_card.max' => 'Identity card may not be larger than 2 MB.',

            'data_truth_confirmation.accepted'
            => 'You must confirm that the submitted information is correct.',

            'terms_accepted.accepted'
            => 'You must agree to the seller terms and KYC data processing policy.',
        ]);

        $latestSubmission = $user->kycSubmissions()
            ->latest('id')
            ->first();

        if ($latestSubmission?->status === 'pending') {
            return redirect()
                ->route('kyc.index')
                ->withErrors([
                    'kyc' => 'You already have a KYC submission under review.',
                ]);
        }

        if ($latestSubmission?->status === 'verified') {
            return redirect()
                ->route('kyc.index')
                ->withErrors([
                    'kyc' => 'Your account has already been verified.',
                ]);
        }

        $verifiedIdentityExists = KycSubmission::query()
            ->where('id_number', $validated['id_number'])
            ->where('status', 'verified')
            ->where('user_id', '!=', $user->id)
            ->exists();

        if ($verifiedIdentityExists) {
            return back()
                ->withInput()
                ->withErrors([
                    'id_number' => 'This NIK is already associated with another verified account.',
                ]);
        }

        $file = $request->file('id_card');

        $fileName = 'id-card-' .
            Str::uuid() .
            '.' .
            $file->getClientOriginalExtension();

        $path = $file->storeAs(
            'kyc/' . $user->id,
            $fileName,
            'private'
        );

        try {
            KycSubmission::create([
                'user_id' => $user->id,
                'full_name' => $validated['full_name'],
                'id_number' => $validated['id_number'],
                'id_card_path' => $path,
                'status' => 'pending',
                'submitted_at' => now(),
                'terms_accepted_at' => now(),
                'policy_version' => config(
                    'rupaia.seller_policy_version'
                ),
            ]);
        } catch (\Throwable $exception) {

            Storage::disk('private')->delete($path);

            throw $exception;
        }

        return redirect()
            ->route('kyc.index')
            ->with(
                'success',
                'Your KYC submission has been submitted successfully.'
            );
    }

    public function show(Request $request, KycSubmission $kyc)
    {
        $user = $request->user();

        if ((int) $kyc->user_id !== (int) $user->id) {
            abort(403);
        }

        if ($kyc->status !== 'verified') {
            abort(403);
        }

        $kyc->load([
            'processor',
        ]);

        return view(
            'kyc.show',
            compact('kyc')
        );
    }

    public function document(Request $request, KycSubmission $kyc)
    {
        $user = $request->user();

        if ((int) $kyc->user_id !== (int) $user->id) {
            abort(403);
        }

        if ($kyc->status !== 'verified') {
            abort(403);
        }

        if (! Storage::disk('private')
            ->exists($kyc->id_card_path)) {
            abort(404);
        }

        $fullPath = Storage::disk('private')
            ->path($kyc->id_card_path);

        return response()->file(
            $fullPath,
            [
                'Content-Disposition' => 'inline',
                'Cache-Control' => 'private, no-store, max-age=0',
                'Pragma' => 'no-cache',
            ]
        );
    }
}
