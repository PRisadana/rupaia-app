<?php

namespace App\Http\Controllers;

use App\Models\Content;
use App\Models\Report;
use App\Models\ShowcaseItem;
use Illuminate\Validation\Rule;

use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function storeContentReport(Request $request, Content $content)
    {
        if ($content->status !== 'active') {
            abort(404);
        }

        if ($content->visibility !== 'public') {
            return back()
                ->withErrors([
                    'report' => 'This content cannot be reported from public page.',
                ]);
        }

        $validated = $request->validate([
            'reason' => [
                'required',
                Rule::in([
                    'copyright',
                    'inappropriate',
                    'misleading',
                    'spam',
                    'privacy',
                    'other',
                ]),
            ],
            'description' => [
                'nullable',
                'string',
                'max:1000',
                Rule::requiredIf($request->reason === 'other'),
            ],
        ]);

        $alreadyReported = Report::where('reporter_id', $request->user()->id)
            ->where('content_id', $content->id)
            ->where('status', 'pending')
            ->exists();

        if ($alreadyReported) {
            return back()
                ->withErrors([
                    'report' => 'You have already reported this content and it is waiting for admin review.',
                ])
                ->withInput();
        }

        Report::create([
            'reporter_id' => $request->user()->id,
            'content_id' => $content->id,
            'showcase_id' => null,
            'reason' => $validated['reason'],
            'description' => $validated['description'] ?? null,
            'status' => 'pending',
        ]);

        return back()->with('success', 'Report submitted successfully. Our admin will review it.');
    }

    public function storeShowcaseReport(Request $request, ShowcaseItem $showcaseItem)
    {
        if ($showcaseItem->status !== 'active') {
            abort(404);
        }

        $validated = $request->validate([
            'reason' => [
                'required',
                Rule::in([
                    'copyright',
                    'inappropriate',
                    'misleading',
                    'spam',
                    'privacy',
                    'other',
                ]),
            ],
            'description' => [
                'nullable',
                'string',
                'max:1000',
                Rule::requiredIf($request->reason === 'other'),
            ],
        ]);

        $alreadyReported = Report::where('reporter_id', $request->user()->id)
            ->where('showcase_id', $showcaseItem->id)
            ->where('status', 'pending')
            ->exists();

        if ($alreadyReported) {
            return back()
                ->withErrors([
                    'report' => 'You have already reported this showcase and it is waiting for admin review.',
                ])
                ->withInput();
        }

        Report::create([
            'reporter_id' => $request->user()->id,
            'content_id' => null,
            'showcase_id' => $showcaseItem->id,
            'reason' => $validated['reason'],
            'description' => $validated['description'] ?? null,
            'status' => 'pending',
        ]);

        return back()->with('success', 'Report submitted successfully. Our admin will review it.');
    }
}
