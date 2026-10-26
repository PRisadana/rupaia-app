<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Content;
use App\Models\Folder;
use App\Models\KycSubmission;
use App\Models\License;
use App\Models\Payout;
use App\Models\Preset;
use App\Models\Report;
use App\Models\ShowcaseItem;
use App\Models\Tags;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Rating;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class AdminDashboardController extends Controller
{
    private const CONTENT_STATUSES = ['active', 'pending_review', 'rejected', 'banned'];

    public function index()
    {
        $contents     = $this->countByStatus(Content::query());
        $folders      = $this->countByStatus(Folder::query());
        $showcases    = $this->countByStatus(ShowcaseItem::query());
        $transactions = $this->countByStatus(Transaction::query());
        $payouts      = $this->countByStatus(Payout::query());
        $kycs         = $this->countByStatus(KycSubmission::query());

        $contentReports  = $this->countByStatus(Report::whereNotNull('content_id'));
        $showcaseReports = $this->countByStatus(Report::whereNotNull('showcase_id'));
        $ratings = $this->countByStatus(Rating::query());

        $pendingReport = fn($query) => $query->where('status', 'pending');

        $attention = [
            [
                'label' => 'Contents pending review',
                'count' => $contents->get('pending_review', 0),
                'url'   => route('admin.content.index', ['status' => 'pending_review']),
            ],
            [
                'label' => 'Payout requests',
                'count' => $payouts->get('pending', 0),
                'url'   => route('admin.payout.index', ['status' => 'pending']),
            ],
            [
                'label' => 'KYC submissions',
                'count' => $kycs->get('pending', 0),
                'url'   => route('admin.kyc.index', ['status' => 'pending']),
            ],
            [
                'label' => 'Reported contents',
                'count' => Content::whereHas('reports', $pendingReport)->count(),
                'url'   => route('admin.report.index', ['status' => 'pending']),
            ],
            [
                'label' => 'Reported showcases',
                'count' => ShowcaseItem::whereHas('reports', $pendingReport)->count(),
                'url'   => route('admin.report.showcase.index', ['status' => 'pending']),
            ],
            [
                'label' => 'Transactions processing',
                'count' => $transactions->get('processing', 0),
                'url'   => route('admin.transaction.index', ['status' => 'processing']),
            ],
        ];

        $sections = [
            [
                'title' => 'Users & Verification',
                'items' => [
                    $this->item('Users', User::count(), 'admin.user.index'),
                    $this->item('KYC submissions', $kycs->sum(), 'admin.kyc.index', $this->breakdown($kycs, KycSubmission::STATUSES)),
                ],
            ],
            [
                'title' => 'Catalog & Moderation',
                'items' => [
                    $this->item('Contents', $contents->sum(), 'admin.content.index', $this->breakdown($contents, self::CONTENT_STATUSES)),
                    $this->item('Folders', $folders->sum(), 'admin.folder.index', $this->breakdown($folders, Folder::STATUSES)),
                    $this->item('Showcases', $showcases->sum(), 'admin.showcase.index', $this->breakdown($showcases, ShowcaseItem::STATUSES)),
                    $this->item('Ratings', $ratings->sum(), 'admin.rating.index', $this->breakdown($ratings, Rating::STATUSES)),
                    $this->item('Content reports', $contentReports->sum(), 'admin.report.index', $this->breakdown($contentReports, Report::STATUSES)),
                    $this->item('Showcase reports', $showcaseReports->sum(), 'admin.report.showcase.index', $this->breakdown($showcaseReports, Report::STATUSES)),
                ],
            ],
            [
                'title' => 'Sales & Payments',
                'items' => [
                    $this->item('Transactions', $transactions->sum(), 'admin.transaction.index', $this->breakdown($transactions, Transaction::STATUSES)),
                    $this->item('Payouts', $payouts->sum(), 'admin.payout.index', $this->breakdown($payouts, Payout::STATUSES)),
                ],
            ],
            [
                'title' => 'Master Data',
                'items' => [
                    $this->item('Presets', Preset::count(), 'admin.preset.index'),
                    $this->item('Tags', Tags::count(), 'admin.tag.index'),
                    $this->item('Licenses', License::count(), 'admin.license.index'),
                ],
            ],
        ];

        return view('admin.dashboard', compact('attention', 'sections'));
    }

    private function countByStatus(Builder $query): Collection
    {
        return $query
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');
    }

    private function breakdown(Collection $counts, array $statuses): array
    {
        return collect($statuses)
            ->mapWithKeys(fn($status) => [$status => (int) $counts->get($status, 0)])
            ->all();
    }

    private function item(string $label, int $total, string $route, array $breakdown = []): array
    {
        return compact('label', 'total', 'route', 'breakdown');
    }
}
