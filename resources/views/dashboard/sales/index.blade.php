@extends('layouts.main')

@section('content')
    <div class="container my-4">
        <div class="p-4 bg-body-tertiary rounded-3 mb-4">
            <h1 class="h3 mb-2">Sales & Earnings</h1>
            <p class="text-muted mb-0">
                Review your completed sales and available earnings.
            </p>
        </div>

        <ul class="nav nav-underline justify-content-center mb-4">
            <li class="nav-item">
                <a class="nav-link text-secondary" href="{{ route('showcase.index') }}">
                    Showcases
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link text-secondary" href="{{ route('content.index') }}">
                    Contents
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link text-secondary" href="{{ route('folder.index') }}">
                    Folders
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link text-secondary" href="{{ route('seller.report.index') }}">
                    Reported Items
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link active" href="{{ route('seller.sales.index') }}">
                    Sales
                </a>
            </li>
        </ul>

        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <div>
                <h5 class="mb-0">Sales Performance</h5>
                <small class="text-muted">The three cards above show all-time totals.</small>
            </div>

            <div class="btn-group btn-group-sm" role="group" aria-label="Period">
                @foreach ($periods as $key => $label)
                    <a href="{{ route('seller.sales.index', ['period' => $key]) }}"
                        class="btn {{ $analytics['period'] === $key ? 'btn-dark' : 'btn-outline-dark' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>
        </div>

        @if ($analytics['totalSold'] === 0)
            <div class="card mb-4">
                <div class="card-body text-center text-muted py-5">
                    No sales data for {{ strtolower($analytics['label']) }}.
                </div>
            </div>
        @else
            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <p class="text-muted mb-2">Revenue ({{ $analytics['label'] }})</p>
                            <h4 class="mb-0">Rp {{ number_format($analytics['totalRevenue'], 0, ',', '.') }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <p class="text-muted mb-2">Items Sold ({{ $analytics['label'] }})</p>
                            <h4 class="mb-0">{{ number_format($analytics['totalSold']) }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <p class="text-muted mb-2">Content vs Bundle</p>
                            <h4 class="mb-0">{{ $analytics['contentSold'] }} / {{ $analytics['bundleSold'] }}</h4>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-lg-8">
                    <div class="card h-100">
                        <div class="card-body">
                            <h6 class="mb-3">Revenue Trend</h6>
                            <canvas id="trendChart" height="110"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <h6 class="mb-3">Sales by Type</h6>
                            <canvas id="typeChart" height="200"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0">Best Sellers</h6>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width:60px">#</th>
                                <th>Item</th>
                                <th>Type</th>
                                <th class="text-end">Sold</th>
                                <th class="text-end">Revenue</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($analytics['bestSellers'] as $best)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $best['name'] }}</td>
                                    <td>
                                        <span class="badge {{ $best['type'] === 'bundle' ? 'bg-dark' : 'bg-secondary' }}">
                                            {{ ucfirst($best['type']) }}
                                        </span>
                                    </td>
                                    <td class="text-end">{{ $best['units'] }}</td>
                                    <td class="text-end">Rp {{ number_format($best['revenue'], 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
            <script>
                const trend = @json($analytics['trend']);

                new Chart(document.getElementById('trendChart'), {
                    type: 'bar',
                    data: {
                        labels: trend.labels,
                        datasets: [{
                            label: 'Revenue',
                            data: trend.revenue,
                            backgroundColor: '#212529'
                        }]
                    },
                    options: {
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                callbacks: {
                                    label: ctx => 'Rp ' + ctx.parsed.y.toLocaleString('id-ID'),
                                    afterLabel: ctx => trend.units[ctx.dataIndex] + ' item(s) sold'
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: v => 'Rp ' + v.toLocaleString('id-ID')
                                }
                            }
                        }
                    }
                });

                new Chart(document.getElementById('typeChart'), {
                    type: 'doughnut',
                    data: {
                        labels: ['Content', 'Bundle'],
                        datasets: [{
                            data: [{{ $analytics['contentSold'] }}, {{ $analytics['bundleSold'] }}],
                            backgroundColor: ['#6c757d', '#212529']
                        }]
                    }
                });
            </script>
        @endif

        <div class="col-md-12">
            <div class="card h-100">
                <div class="card-body">
                    <p class="text-muted mb-2">
                        Available Balance
                    </p>

                    <h3 class="mb-0">
                        Rp {{ number_format($availableBalance, 0, ',', '.') }}
                    </h3>

                    <div class="mt-3">
                        <a href="{{ route('seller.payout.index') }}" class="btn btn-sm btn-dark">
                            Request Payout
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0">
                Sales History
            </h5>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Order</th>
                        <th>Sold At</th>
                        <th>Item</th>
                        <th>Type</th>
                        <th class="text-end">Sale Price</th>
                        <th class="text-end">Commission</th>
                        <th class="text-end">Your Revenue</th>
                        <th>Status</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($sales as $sale)
                        <tr>
                            <td>
                                <span class="fw-semibold">
                                    {{ $sale->transaction->order_code }}
                                </span>
                            </td>
                            <td>
                                {{ $sale->transaction->paid_at ? $sale->transaction->paid_at->format('d M Y H:i') : '-' }}
                            </td>
                            <td>
                                {{ $sale->item_name_snapshot }}
                            </td>
                            <td>
                                @if ($sale->item_type === 'bundle')
                                    <span class="badge bg-dark">
                                        Bundle
                                    </span>
                                @else
                                    <span class="badge bg-secondary">
                                        Content
                                    </span>
                                @endif
                            </td>
                            <td class="text-end">
                                Rp {{ number_format($sale->price_snapshot, 0, ',', '.') }}
                            </td>
                            <td class="text-end">
                                Rp {{ number_format($sale->commission_amount, 0, ',', '.') }}
                            </td>
                            <td class="text-end fw-semibold">
                                Rp {{ number_format($sale->seller_amount, 0, ',', '.') }}
                            </td>
                            <td>
                                @if (!$sale->payout_id)
                                    <span class="badge bg-success">
                                        Available
                                    </span>
                                @elseif ($sale->payout?->status === 'pending')
                                    <span class="badge bg-warning text-dark">
                                        Pending Payout
                                    </span>
                                @elseif ($sale->payout?->status === 'paid')
                                    <span class="badge bg-secondary">
                                        Paid Out
                                    </span>
                                @else
                                    <span class="badge bg-light text-dark border">
                                        Unavailable
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-5">
                                No completed sales{{ $analytics['period'] === 'all' ? ' yet' : ' in this period' }}.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($sales->hasPages())
            <div class="card-footer bg-white">
                {{ $sales->links() }}
            </div>
        @endif
    </div>
    </div>
@endsection
