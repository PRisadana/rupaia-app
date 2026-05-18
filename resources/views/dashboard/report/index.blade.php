@extends('layouts.main')

@section('content')
    <div class="container my-4">
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        <div class="p-5 text-center bg-body-tertiary rounded-3">
            <img src="{{ Auth::user()->profile_photo_path ? asset('storage/' . Auth::user()->profile_photo_path) : asset('aset/rupaia_logo.png') }}"
                alt="Profile" class="rounded-circle" width="100" height="100" style="object-fit: cover;">
            <h1 class="text-body-emphasis my-3">{{ Auth::user()->name }}</h1>
            <p class="col-lg-8 mx-auto my-3 fs-5 text-muted">
                {{ Auth::user()->bio ?? 'This user has not set a bio yet.' }}
            </p>
            <div class="d-inline-flex gap-2 my-3">
                <button class="d-inline-flex align-items-center btn btn-dark px-4 rounded-pill" type="button">
                    <a class="text-white nav-link" href="{{ route('profile.edit') }}">{{ __('Profile Setting') }}</a>
                </button>
            </div>
        </div>
    </div>

    <ul class="nav nav-underline justify-content-center">
        <li class="nav-item">
            <a class="nav-link text-secondary" aria-current="page" href="{{ route('showcase.index') }}">Showcases</a>
        </li>
        <li class="nav-item">
            <a class="nav-link text-secondary" href="{{ route('content.index') }}">Contents</a>
        </li>
        <li class="nav-item">
            <a class="nav-link text-secondary" aria-current="page" href="{{ route('folder.index') }}">Folders</a>
        </li>
        <li class="nav-item">
            <a class="nav-link active" aria-current="page" href="{{ route('seller.report.index') }}">Reported Items</a>
        </li>
    </ul>

    <div class="container my-4">
        @if ($reportedItems->count())
            <div class="row g-4">
                @foreach ($reportedItems as $reportedItem)
                    @php
                        $target = $reportedItem->target;
                        $latestReport = $reportedItem->latest_report;

                        if ($reportedItem->type === 'content') {
                            $previewPath = $target->path_low_res;
                            $title = $target->content_title;
                            $detailRoute = route('content.detail', $target->id);
                            $typeLabel = 'Published Content';
                            $typeBadge = 'bg-success';
                        } else {
                            $previewPath = $target->custom_path ?? $target->content?->path_low_res;
                            $title = $target->description ?: 'Showcase #' . $target->id;
                            $detailRoute = route('authors.show.detail', $target->id);
                            $typeLabel = 'Showcase Content';
                            $typeBadge = 'bg-danger';
                        }

                        $reasonLabels = [
                            'copyright' => 'Copyright infringement',
                            'inappropriate' => 'Inappropriate content',
                            'misleading' => 'Misleading content',
                            'spam' => 'Spam or scam',
                            'privacy' => 'Privacy violation',
                            'other' => 'Other',
                        ];
                    @endphp

                    <div class="col-12">
                        <div class="card border-0 shadow-sm rounded-4">
                            <div class="card-body p-4">
                                <div class="row g-4 align-items-start">
                                    <div class="col-md-3">
                                        <img src="{{ $previewPath ? asset('storage/' . $previewPath) : asset('aset/rupaia_logo.png') }}"
                                            alt="{{ $title }}" class="img-fluid rounded-4 w-100"
                                            style="height: 180px; object-fit: cover;">
                                    </div>

                                    <div class="col-md-9">
                                        <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap mb-2">
                                            <div>
                                                <span class="badge {{ $typeBadge }} mb-2">
                                                    {{ $typeLabel }}
                                                </span>

                                                <h4 class="fw-bold mb-1">
                                                    <a href="{{ $detailRoute }}" class="text-dark text-decoration-none">
                                                        {{ $title }}
                                                    </a>
                                                </h4>

                                                <p class="text-muted mb-0">
                                                    Total reviewed reports:
                                                    <strong>{{ $reportedItem->total_reports_count }}</strong>
                                                </p>
                                            </div>

                                            <div class="text-end">
                                                @if ($latestReport->action_taken === 'takedown')
                                                    <span class="badge bg-danger px-3 py-2">
                                                        Takedown
                                                    </span>
                                                @elseif ($latestReport->action_taken === 'ignored')
                                                    <span class="badge bg-secondary px-3 py-2">
                                                        Ignored
                                                    </span>
                                                @else
                                                    <span class="badge bg-light text-dark border px-3 py-2">
                                                        No Action
                                                    </span>
                                                @endif
                                            </div>
                                        </div>

                                        <hr>

                                        <div class="mb-3 d-flex flex-wrap gap-2">
                                            <span class="badge bg-secondary">
                                                Ignored: {{ $reportedItem->ignored_reports_count }}
                                            </span>

                                            <span class="badge bg-success">
                                                Resolved: {{ $reportedItem->resolved_reports_count }}
                                            </span>

                                            @if ($target->status === 'banned')
                                                <span class="badge bg-danger">
                                                    Item Status: Banned
                                                </span>
                                            @elseif ($target->status === 'active')
                                                <span class="badge bg-success">
                                                    Item Status: Active
                                                </span>
                                            @else
                                                <span class="badge bg-dark">
                                                    Item Status: {{ ucfirst($target->status) }}
                                                </span>
                                            @endif
                                        </div>

                                        @if ($latestReport->admin_note)
                                            <div class="alert alert-light border mb-3">
                                                <strong>Admin Note:</strong><br>
                                                {{ $latestReport->admin_note }}
                                            </div>
                                        @endif

                                        <div class="accordion"
                                            id="reportAccordion-{{ $reportedItem->type }}-{{ $target->id }}">
                                            <div class="accordion-item">
                                                <h2 class="accordion-header">
                                                    <button class="accordion-button collapsed" type="button"
                                                        data-bs-toggle="collapse"
                                                        data-bs-target="#reportDetails-{{ $reportedItem->type }}-{{ $target->id }}">
                                                        View Report Details
                                                    </button>
                                                </h2>

                                                <div id="reportDetails-{{ $reportedItem->type }}-{{ $target->id }}"
                                                    class="accordion-collapse collapse"
                                                    data-bs-parent="#reportAccordion-{{ $reportedItem->type }}-{{ $target->id }}">
                                                    <div class="accordion-body">
                                                        @foreach ($reportedItem->reports as $report)
                                                            <div class="border rounded-3 p-3 mb-3">
                                                                <div
                                                                    class="d-flex justify-content-between align-items-start gap-3">
                                                                    <div>
                                                                        <div class="fw-semibold">
                                                                            {{ $reasonLabels[$report->reason] ?? ucfirst($report->reason) }}
                                                                        </div>

                                                                        <div class="small text-muted">
                                                                            Reported at:
                                                                            {{ $report->created_at->format('d M Y H:i') }}
                                                                        </div>
                                                                    </div>

                                                                    <div>
                                                                        @if ($report->status === 'resolved')
                                                                            <span class="badge bg-success">Resolved</span>
                                                                        @elseif ($report->status === 'ignored')
                                                                            <span class="badge bg-secondary">Ignored</span>
                                                                        @else
                                                                            <span class="badge bg-light text-dark border">
                                                                                {{ ucfirst($report->status) }}
                                                                            </span>
                                                                        @endif
                                                                    </div>
                                                                </div>

                                                                <div class="mt-3">
                                                                    <div class="small text-muted mb-1">Description</div>
                                                                    <p class="mb-0">
                                                                        {{ $report->description ?: 'No description provided.' }}
                                                                    </p>
                                                                </div>

                                                                @if ($report->processed_at)
                                                                    <div class="small text-muted mt-3">
                                                                        Processed at:
                                                                        {{ \Carbon\Carbon::parse($report->processed_at)->format('d M Y H:i') }}
                                                                    </div>
                                                                @endif

                                                                @if ($report->admin_note)
                                                                    <div class="alert alert-light border mt-3 mb-0">
                                                                        <strong>Admin Note:</strong><br>
                                                                        {{ $report->admin_note }}
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="my-4">
                {{ $reportedItems->links() }}
            </div>
        @else
            <div class="text-center py-5 bg-light rounded-4">
                <h4 class="fw-bold mb-2">No Reviewed Reports</h4>
                <p class="text-muted mb-0">
                    There are no reviewed reports for your contents or showcases yet.
                </p>
            </div>
        @endif
    </div>
@endsection
