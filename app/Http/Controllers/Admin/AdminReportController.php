<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Content;
use App\Models\Report;
use App\Models\ShowcaseItem;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AdminReportController extends Controller
{
    public function index(Request $request)
    {
        $reportedContents = Content::query()
            ->whereHas('reports')
            ->with([
                'user',
                'reports' => function ($query) {
                    $query->with(['reporter', 'processor'])
                        ->latest();
                },
            ])
            ->withCount([
                'reports as pending_reports_count' => function ($query) {
                    $query->where('status', 'pending');
                },
                'reports as ignored_reports_count' => function ($query) {
                    $query->where('status', 'ignored');
                },
                'reports as resolved_reports_count' => function ($query) {
                    $query->where('status', 'resolved');
                },
                'reports as total_reports_count',
            ])
            ->latest()
            ->paginate(10);

        return view('admin.report.index', compact('reportedContents'));
    }

    public function show(Content $content)
    {
        $content->load([
            'user',
            'folder',
            'reports' => function ($query) {
                $query->with(['reporter', 'processor'])->latest();
            },
        ]);

        $reasonSummary = $content->reports()
            ->select('reason', DB::raw('count(*) as total'))
            ->groupBy('reason')
            ->pluck('total', 'reason');

        $pendingReportsCount = $content->reports()
            ->where('status', 'pending')
            ->count();

        return view('admin.report.show', compact('content', 'reasonSummary', 'pendingReportsCount'));
    }

    public function processContentReports(Request $request, Content $content)
    {
        $validated = $request->validate([
            'action_taken' => ['required', Rule::in(['ignored', 'takedown'])],
            'admin_note' => ['nullable', 'string', 'max:1000'],
        ]);

        $pendingReports = Report::where('content_id', $content->id)
            ->where('status', 'pending')
            ->get();

        if ($pendingReports->isEmpty()) {
            return redirect()
                ->route('admin.report.show', $content->id)
                ->withErrors([
                    'reports' => 'There are no pending reports for this content.',
                ]);
        }

        DB::transaction(function () use ($validated, $pendingReports, $content, $request) {
            if ($validated['action_taken'] === 'takedown') {
                $content->update([
                    'status' => 'banned',
                ]);

                foreach ($pendingReports as $report) {
                    $report->update([
                        'status' => 'resolved',
                        'action_taken' => 'takedown',
                        'admin_note' => $validated['admin_note'] ?? null,
                        'processed_by' => $request->user()->id,
                        'processed_at' => now(),
                    ]);
                }
            }

            if ($validated['action_taken'] === 'ignored') {
                foreach ($pendingReports as $report) {
                    $report->update([
                        'status' => 'ignored',
                        'action_taken' => 'ignored',
                        'admin_note' => $validated['admin_note'] ?? null,
                        'processed_by' => $request->user()->id,
                        'processed_at' => now(),
                    ]);
                }
            }
        });

        return redirect()
            ->route('admin.report.showcase.index')
            ->with('success', 'Reports processed successfully.');
    }

    public function indexShowcase(Request $request)
    {
        $reportedShowcases = ShowcaseItem::query()
            ->whereHas('reports')
            ->with([
                'user',
                'reports' => function ($query) {
                    $query->with(['reporter', 'processor'])
                        ->latest();
                },
            ])
            ->withCount([
                'reports as pending_reports_count' => function ($query) {
                    $query->where('status', 'pending');
                },
                'reports as ignored_reports_count' => function ($query) {
                    $query->where('status', 'ignored');
                },
                'reports as resolved_reports_count' => function ($query) {
                    $query->where('status', 'resolved');
                },
                'reports as total_reports_count',
            ])
            ->latest()
            ->paginate(10);

        return view('admin.report.showcase-index', compact('reportedShowcases'));
    }

    public function showcaseShow(ShowcaseItem $showcaseItem)
    {
        $showcaseItem->load([
            'user',
            'reports' => function ($query) {
                $query->with(['reporter', 'processor'])->latest();
            },
        ]);

        $reasonSummary = $showcaseItem->reports()
            ->select('reason', DB::raw('count(*) as total'))
            ->groupBy('reason')
            ->pluck('total', 'reason');

        $pendingReportsCount = $showcaseItem->reports()
            ->where('status', 'pending')
            ->count();

        return view('admin.report.showcase-show', compact('showcaseItem', 'reasonSummary', 'pendingReportsCount'));
    }

    public function processShowcaseReports(Request $request, ShowcaseItem $showcaseItem)
    {
        $validated = $request->validate([
            'action_taken' => ['required', Rule::in(['ignored', 'takedown'])],
            'admin_note' => ['nullable', 'string', 'max:1000'],
        ]);

        $pendingReports = Report::where('showcase_id', $showcaseItem->id)
            ->where('status', 'pending')
            ->get();

        if ($pendingReports->isEmpty()) {
            return redirect()
                ->route('admin.report.showcase.show', $showcaseItem->id)
                ->withErrors([
                    'reports' => 'There are no pending reports for this showcase item.',
                ]);
        }

        DB::transaction(function () use ($validated, $pendingReports, $showcaseItem, $request) {
            if ($validated['action_taken'] === 'takedown') {
                $showcaseItem->update([
                    'status' => 'banned',
                ]);

                foreach ($pendingReports as $report) {
                    $report->update([
                        'status' => 'resolved',
                        'action_taken' => 'takedown',
                        'admin_note' => $validated['admin_note'] ?? null,
                        'processed_by' => $request->user()->id,
                        'processed_at' => now(),
                    ]);
                }
            }

            if ($validated['action_taken'] === 'ignored') {
                foreach ($pendingReports as $report) {
                    $report->update([
                        'status' => 'ignored',
                        'action_taken' => 'ignored',
                        'admin_note' => $validated['admin_note'] ?? null,
                        'processed_by' => $request->user()->id,
                        'processed_at' => now(),
                    ]);
                }
            }
        });

        return redirect()
            ->route('admin.report.showcase.index')
            ->with('success', 'Reports processed successfully.');
    }
}
