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

        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-body">
                        <p class="text-muted mb-2">
                            Total Sales Revenue
                        </p>

                        <h3 class="mb-0">
                            Rp {{ number_format($totalRevenue, 0, ',', '.') }}
                        </h3>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
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

            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-body">
                        <p class="text-muted mb-2">
                            Items Sold
                        </p>

                        <h3 class="mb-0">
                            {{ number_format($totalItemsSold) }}
                        </h3>

                        <hr>

                        <p>Content = {{ $totalContentsSold }}</p>
                        <p>Bundle = {{ $totalBundlesSold }}</p>
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
                                    No completed sales yet.
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
