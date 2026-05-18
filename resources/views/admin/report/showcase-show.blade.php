@extends('layouts.admin')

@section('content')
    <div class="container-fluid px-4">

        <div class="d-flex justify-content-between align-items-center my-4">
            <h1 class="m-0">Review Showcase Reports</h1>

            <a href="{{ route('admin.report.index') }}" class="btn btn-outline-secondary">
                ← Back to Reports
            </a>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>Some errors occurred:</strong>
                <ul class="mb-0 mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="row g-4">

            {{-- LEFT: Showcase Detail --}}
            <div class="col-lg-4">
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <div class="content-clean-wrapper shadow-sm">
                            @php
                                $imagePath = $showcaseItem->custom_path ?? $showcaseItem->content?->path_low_res;
                            @endphp
                            <img src="{{ $imagePath ? asset('storage/' . $imagePath) : asset('aset/rupaia_logo.png') }}"
                                alt="{{ $showcaseItem->description ?? 'No caption available.' }}"
                                class="img-fluid rounded mb-3 w-100 content-clean-image" loading="lazy">
                        </div>

                        <h4 class="fw-bold my-2">{{ $showcaseItem->description }}</h4>

                        <p class="text-muted my-2">
                            Author:
                            <a href="{{ route('authors.show', $showcaseItem->user->id) }}" class="text-dark fw-semibold">
                                {{ $showcaseItem->user->name }}
                            </a>
                        </p>

                        <p class="my-2">
                            Showcase Status:
                            @if ($showcaseItem->status === 'active')
                                <span class="badge bg-success">Active</span>
                            @elseif ($showcaseItem->status === 'banned')
                                <span class="badge bg-danger">Banned</span>
                            @else
                                <span class="badge bg-secondary">{{ ucfirst($showcaseItem->status) }}</span>
                            @endif
                        </p>

                    </div>
                </div>

                {{-- Process Form --}}
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h5 class="fw-bold mb-3">Take Action</h5>

                        <p class="text-muted small">
                            This action will process all pending reports for this showcase.
                        </p>

                        <p class="mb-3">
                            Pending Reports:
                            <span class="badge bg-warning text-dark">
                                {{ $pendingReportsCount }}
                            </span>
                        </p>

                        <form action="{{ route('admin.report.showcase.process', $showcaseItem->id) }}" method="POST">
                            @csrf
                            @method('PATCH')

                            <div class="mb-3">
                                <label for="action_taken" class="form-label">Action Taken</label>
                                <select name="action_taken" id="action_taken"
                                    class="form-select @error('action_taken') is-invalid @enderror" required
                                    {{ $pendingReportsCount < 1 ? 'disabled' : '' }}>
                                    <option value="">Select Action</option>
                                    <option value="ignored" {{ old('action_taken') === 'ignored' ? 'selected' : '' }}>
                                        Ignore
                                    </option>
                                    <option value="takedown" {{ old('action_taken') === 'takedown' ? 'selected' : '' }}>
                                        Takedown
                                    </option>
                                </select>

                                @error('action_taken')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="admin_note" class="form-label">Admin Note</label>
                                <textarea name="admin_note" id="admin_note" class="form-control @error('admin_note') is-invalid @enderror"
                                    rows="4" placeholder="Optional note for seller or internal review."
                                    {{ $pendingReportsCount < 1 ? 'disabled' : '' }}>{{ old('admin_note') }}</textarea>

                                @error('admin_note')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <button type="submit" class="btn btn-dark w-100"
                                {{ $pendingReportsCount < 1 ? 'disabled' : '' }}
                                onclick="return confirm('Process all pending reports for this content?')">
                                Process Reports
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            {{-- RIGHT: Reports --}}
            <div class="col-lg-8">

                {{-- Summary --}}
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <h5 class="fw-bold mb-3">Reason Summary</h5>

                        @php
                            $reasonLabels = [
                                'copyright' => 'Copyright infringement',
                                'inappropriate' => 'Inappropriate content',
                                'misleading' => 'Misleading content',
                                'spam' => 'Spam or scam',
                                'privacy' => 'Privacy violation',
                                'other' => 'Other',
                            ];
                        @endphp

                        @if ($reasonSummary->count())
                            <div class="d-flex flex-wrap gap-2">
                                @foreach ($reasonSummary as $reason => $total)
                                    <span class="badge bg-light text-dark border px-3 py-2">
                                        {{ $reasonLabels[$reason] ?? ucfirst($reason) }}:
                                        {{ $total }}
                                    </span>
                                @endforeach
                            </div>
                        @else
                            <p class="text-muted mb-0">No reports found.</p>
                        @endif
                    </div>
                </div>

                {{-- Report Details --}}
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h5 class="fw-bold mb-3">Report Details</h5>

                        @forelse ($showcaseItem->reports as $report)
                            <div class="border rounded-3 p-3 mb-3">
                                <div class="d-flex justify-content-between align-items-start gap-3">

                                    <div>
                                        <div class="fw-semibold">
                                            {{ $report->reason_label }}
                                        </div>

                                        <div class="small text-muted">
                                            Reported by:
                                            {{ $report->reporter->name ?? 'Unknown User' }}
                                            · {{ $report->created_at->format('d M Y H:i') }}
                                        </div>
                                    </div>

                                    <div>
                                        @if ($report->status === 'pending')
                                            <span class="badge bg-warning text-dark">Pending</span>
                                        @elseif ($report->status === 'resolved')
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

                                @if ($report->action_taken)
                                    <hr>

                                    <div class="small text-muted">
                                        Action:
                                        <strong>{{ ucfirst($report->action_taken) }}</strong>

                                        @if ($report->processor)
                                            · Processed by {{ $report->processor->name }}
                                        @endif

                                        @if ($report->processed_at)
                                            · {{ \Carbon\Carbon::parse($report->processed_at)->format('d M Y H:i') }}
                                        @endif
                                    </div>
                                @endif

                                @if ($report->admin_note)
                                    <div class="alert alert-light border mt-3 mb-0">
                                        <strong>Admin Note:</strong><br>
                                        {{ $report->admin_note }}
                                    </div>
                                @endif
                            </div>
                        @empty
                            <div class="text-center text-muted py-5">
                                No reports available.
                            </div>
                        @endforelse

                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection
