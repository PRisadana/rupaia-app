<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KycSubmission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;

class AdminKycController extends Controller
{
    public function index(Request $request)
    {
        $query = KycSubmission::with([
            'user',
            'processor',
        ]);

        $status = $request->query('status');

        if (in_array($status, [
            'pending',
            'verified',
            'rejected',
        ])) {
            $query->where('status', $status);
        }

        $submissions = $query
            ->latest('submitted_at')
            ->paginate(10)
            ->withQueryString();

        return view(
            'admin.kyc.index',
            compact('submissions', 'status')
        );
    }

    public function show(KycSubmission $kyc)
    {
        $kyc->load([
            'user',
            'processor',
        ]);

        return view(
            'admin.kyc.show',
            compact('kyc')
        );
    }

    public function document(KycSubmission $kyc)
    {
        if (! Storage::disk('private')
            ->exists($kyc->id_card_path)) {

            abort(404);
        }

        $fullPath = Storage::disk('private')
            ->path($kyc->id_card_path);

        return response()->file($fullPath);
    }

    public function verify(KycSubmission $kyc)
    {
        DB::transaction(function () use ($kyc) {

            $submission = KycSubmission::query()
                ->whereKey($kyc->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($submission->status !== 'pending') {
                throw ValidationException::withMessages([
                    'kyc' => 'Only pending KYC submissions can be verified.',
                ]);
            }

            $identityAlreadyVerified = KycSubmission::query()
                ->where('id_number', $submission->id_number)
                ->where('status', 'verified')
                ->where('id', '!=', $submission->id)
                ->exists();

            if ($identityAlreadyVerified) {
                throw ValidationException::withMessages([
                    'kyc' => 'This NIK has already been verified for another account.',
                ]);
            }

            $submission->update([
                'status' => 'verified',
                'processed_by' => Auth::id(),
                'processed_at' => now(),
                'admin_note' => null,
            ]);

            $submission->user()->update([
                'role' => 'seller',
            ]);
        });

        return redirect()
            ->route('admin.kyc.show', $kyc)
            ->with(
                'success',
                'KYC has been verified. The user now has seller access.'
            );
    }

    public function reject(
        Request $request,
        KycSubmission $kyc
    ) {
        $validated = $request->validate([
            'admin_note' => [
                'required',
                'string',
                'max:1000',
            ],
        ], [
            'admin_note.required'
            => 'Rejection reason is required.',
            'admin_note.max'
            => 'Rejection reason may not exceed 1000 characters.',
        ]);

        DB::transaction(function () use (
            $kyc,
            $validated
        ) {

            $submission = KycSubmission::query()
                ->whereKey($kyc->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($submission->status !== 'pending') {
                throw ValidationException::withMessages([
                    'kyc' => 'Only pending KYC submissions can be rejected.',
                ]);
            }

            $submission->update([
                'status' => 'rejected',
                'processed_by' => Auth::id(),
                'processed_at' => now(),
                'admin_note' => $validated['admin_note'],
            ]);
        });

        return redirect()
            ->route('admin.kyc.show', $kyc)
            ->with(
                'success',
                'KYC submission has been rejected.'
            );
    }
}
