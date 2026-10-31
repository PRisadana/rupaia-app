<?php

namespace App\Http\Controllers\Rating;

use App\Http\Controllers\Controller;
use App\Models\Rating;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminRatingController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'q'            => ['nullable', 'string', 'max:100'],
            'status'       => ['nullable', Rule::in(Rating::STATUSES)],
            'rating_value' => ['nullable', 'integer', 'between:1,5'],
        ]);

        $ratings = Rating::query()
            ->filterAdmin($filters)
            ->with(['transactionItem.transaction.buyer', 'transactionItem.seller', 'moderator'])
            ->latest('updated_at')
            ->paginate(10)
            ->withQueryString();

        return view('admin.rating.index', compact('ratings'));
    }

    public function updateStatus(Request $request, Rating $rating)
    {
        $rating->timestamps = false;

        $validated = $request->validate([
            'status'          => ['required', Rule::in(Rating::STATUSES)],
            'moderation_note' => [
                'nullable',
                'string',
                'max:500',
                Rule::requiredIf($request->input('status') === Rating::HIDDEN),
            ],
        ]);

        $rating->update([
            'status'          => $validated['status'],
            'moderated_by'    => $request->user()->id,
            'moderated_at'    => now(),
            'moderation_note' => $validated['moderation_note'] ?? null,
        ]);

        return back()->with(
            'success',
            $validated['status'] === Rating::HIDDEN ? 'Rating hidden.' : 'Rating is visible again.'
        );
    }
}
