<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class SellerReportController extends Controller
{
    public function index(Request $request)
    {
        $sellerId = $request->user()->id;

        $reports = Report::with([
            'content.user',
            'showcase.user',
            'processor',
        ])
            ->whereIn('status', ['ignored', 'resolved'])
            ->where(function ($query) use ($sellerId) {
                $query->whereHas('content', function ($contentQuery) use ($sellerId) {
                    $contentQuery->where('seller_id', $sellerId);
                })
                    ->orWhereHas('showcase', function ($showcaseQuery) use ($sellerId) {
                        $showcaseQuery->where('seller_id', $sellerId);
                    });
            })
            ->latest('processed_at')
            ->get();

        $groupedReports = $reports
            ->groupBy(function ($report) {
                if ($report->content_id) {
                    return 'content-' . $report->content_id;
                }

                if ($report->showcase_id) {
                    return 'showcase-' . $report->showcase_id;
                }

                return 'unknown-' . $report->id;
            })
            ->map(function ($reports) {
                $latestReport = $reports->first();

                $type = $latestReport->content_id ? 'content' : 'showcase';

                $target = $type === 'content'
                    ? $latestReport->content
                    : $latestReport->showcase;

                if (! $target) {
                    return null;
                }

                return (object) [
                    'type' => $type,
                    'target' => $target,
                    'latest_report' => $latestReport,
                    'reports' => $reports,
                    'ignored_reports_count' => $reports->where('status', 'ignored')->count(),
                    'resolved_reports_count' => $reports->where('status', 'resolved')->count(),
                    'total_reports_count' => $reports->count(),
                ];
            })
            ->filter()
            ->values();

        $page = LengthAwarePaginator::resolveCurrentPage();
        $perPage = 10;

        $reportedItems = new LengthAwarePaginator(
            $groupedReports->forPage($page, $perPage)->values(),
            $groupedReports->count(),
            $perPage,
            $page,
            [
                'path' => request()->url(),
                'query' => request()->query(),
            ]
        );

        return view('dashboard.report.index', compact('reportedItems'));
    }
}
