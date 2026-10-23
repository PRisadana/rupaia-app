@extends('layouts.admin')

@section('content')
    @php
        $badgeClass = [
            'active' => 'bg-success',
            'completed' => 'bg-success',
            'paid' => 'bg-success',
            'verified' => 'bg-success',
            'resolved' => 'bg-success',
            'pending' => 'bg-warning text-dark',
            'pending_review' => 'bg-warning text-dark',
            'processing' => 'bg-info text-dark',
            'rejected' => 'bg-danger',
            'banned' => 'bg-danger',
            'cancelled' => 'bg-secondary',
            'ignored' => 'bg-secondary',
        ];
    @endphp

    <div class="container-fluid px-4 admin-page-wrapper">
        <div class="my-4 admin-page-title">
            <h1 class="m-0">Dashboard</h1>
            <p class="text-muted mb-0">Overview of everything in the Rupaia system.</p>
        </div>

        <h5 class="mb-3">Needs attention</h5>
        <div class="row g-3 mb-4">
            @foreach ($attention as $item)
                <div class="col-xl-2 col-md-4 col-6">
                    <a href="{{ $item['url'] }}" class="text-decoration-none">
                        <div class="card admin-card h-100 {{ $item['count'] > 0 ? 'border-warning border-2' : '' }}">
                            <div class="card-body">
                                <div class="h2 mb-1 text-dark">{{ number_format($item['count']) }}</div>
                                <div class="small text-muted">{{ $item['label'] }}</div>
                            </div>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>

        @foreach ($sections as $section)
            <h5 class="mb-3">{{ $section['title'] }}</h5>
            <div class="row g-3 mb-4">
                @foreach ($section['items'] as $item)
                    <div class="col-xl-4 col-md-6">
                        <div class="card admin-card h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <div class="small text-muted">{{ $item['label'] }}</div>
                                        <div class="h3 mb-0">{{ number_format($item['total']) }}</div>
                                    </div>
                                    <a href="{{ route($item['route']) }}" class="btn btn-sm btn-outline-dark">View</a>
                                </div>

                                @if ($item['breakdown'])
                                    <div class="d-flex flex-wrap gap-2 mt-3">
                                        @foreach ($item['breakdown'] as $status => $count)
                                            <a href="{{ route($item['route'], ['status' => $status]) }}"
                                                class="badge text-decoration-none {{ $badgeClass[$status] ?? 'bg-secondary' }}">
                                                {{ ucfirst(str_replace('_', ' ', $status)) }}: {{ $count }}
                                            </a>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endforeach
    </div>
@endsection
