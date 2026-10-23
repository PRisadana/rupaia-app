@extends('layouts.admin')

@section('content')
    <div class="container-fluid px-4 admin-page-wrapper">
        <div class="d-flex justify-content-between align-items-center my-4 admin-page-title">
            <div>
                <h1 class="m-0">Showcase Reports</h1>
                <p class="text-muted mb-0">
                    Review reports submitted against showcase items.
                </p>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="admin-table-wrapper">
            @include('admin.partials.filter-bar', [
                'action' => route('admin.report.showcase.index'),
                'filters' => [['name' => 'status', 'label' => 'Status', 'options' => App\Models\Report::STATUSES]],
            ])

            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead>
                        <tr>
                            <th scope="col">No</th>
                            <th scope="col">Showcase</th>
                            <th scope="col">Seller</th>
                            <th scope="col">Report Status</th>
                            <th scope="col">Action Taken</th>
                            <th scope="col">Pending Reports</th>
                            <th scope="col">Total Reports</th>
                            <th scope="col">Latest Reason</th>
                            <th scope="col">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($reportedShowcases as $showcaseItem)
                            @php
                                $latestReport = $showcaseItem->reports->first();
                            @endphp

                            <tr>
                                <td>{{ $reportedShowcases->firstItem() + $loop->index }}</td>
                                <td>
                                    <a href="{{ route('authors.show.detail', $showcaseItem->id) }}"
                                        class="text-dark fw-semibold">
                                        {{ \Illuminate\Support\Str::limit($showcaseItem->description, 60) }}
                                    </a>
                                </td>
                                <td>
                                    @if ($showcaseItem->user)
                                        <a href="{{ route('authors.show', $showcaseItem->user->id) }}"
                                            class="text-dark fw-semibold">
                                            {{ $showcaseItem->user->name }}
                                        </a>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($latestReport?->status === 'pending')
                                        <span class="badge bg-warning text-dark">Pending</span>
                                    @elseif ($latestReport?->status === 'ignored')
                                        <span class="badge bg-secondary">Ignored</span>
                                    @elseif ($latestReport?->status === 'resolved')
                                        <span class="badge bg-success">Resolved</span>
                                    @elseif ($latestReport)
                                        <span class="badge bg-light text-dark border">
                                            {{ ucfirst($latestReport->status) }}
                                        </span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($latestReport?->action_taken === 'takedown')
                                        <span class="badge bg-danger">Takedown</span>
                                    @elseif ($latestReport?->action_taken === 'ignored')
                                        <span class="badge bg-secondary">Ignored</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-warning text-dark">
                                        {{ $showcaseItem->pending_reports_count ?? 0 }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-secondary">
                                        {{ $showcaseItem->total_reports_count ?? 0 }}
                                    </span>
                                </td>
                                <td>
                                    @if ($latestReport)
                                        <span class="badge bg-light text-dark border">
                                            {{ $latestReport->reason_label ?? ucfirst($latestReport->reason) }}
                                        </span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('admin.report.showcase.show', $showcaseItem->id) }}"
                                        class="btn btn-sm btn-outline-primary" title="View Detail">
                                        <i class="fi fi-rr-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-4 text-muted">
                                    {{ request()->filled('status') ? 'No reported showcases match this filter.' : 'No reported showcases found.' }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                {{ $reportedShowcases->links() }}
            </div>
        </div>
    </div>
@endsection
