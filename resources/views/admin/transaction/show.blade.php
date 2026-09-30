@extends('layouts.admin')

@section('content')
    <div class="container-fluid px-4 admin-page-wrapper">
        <div class="d-flex justify-content-between align-items-center
                flex-wrap gap-3 my-4 admin-page-title">
            <div>
                <h1 class="m-0 mt-2">
                    Transaction Detail
                </h1>
                <p class="text-muted mb-0">
                    {{ $transaction->order_code }}
                </p>

            </div>

            @if ($transaction->snap_token)
                <form method="POST" action="{{ route('admin.transaction.sync-status', $transaction) }}">
                    @csrf
                    <button type="submit" class="btn btn-outline-dark">
                        <i class="fi fi-rr-refresh me-1"></i>
                        Synchronize Status
                    </button>
                </form>
            @endif
        </div>

        @if (session('success'))
            <div class="alert alert-success
                    alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert">
                </button>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger
                    alert-dismissible fade show" role="alert">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
                <button type="button" class="btn-close" data-bs-dismiss="alert">
                </button>
            </div>
        @endif

        <div class="card mb-4">
            <div
                class="card-header
                    d-flex justify-content-between
                    align-items-center">
                <span class="fw-semibold">
                    Transaction Information
                </span>
                <div class="d-flex gap-2">
                    @if ($transaction->status === 'completed')
                        <span class="badge bg-success">
                            Completed
                        </span>
                    @elseif ($transaction->status === 'pending')
                        <span class="badge bg-warning text-dark">
                            Pending
                        </span>
                    @else
                        <span class="badge bg-secondary">
                            {{ ucfirst($transaction->status) }}
                        </span>
                    @endif

                    @if ($transaction->payment_status === 'success')
                        <span class="badge bg-success">
                            Payment Success
                        </span>
                    @elseif ($transaction->payment_status === 'pending')
                        <span class="badge bg-warning text-dark">
                            Payment Pending
                        </span>
                    @elseif ($transaction->payment_status === 'failed')
                        <span class="badge bg-danger">
                            Payment Failed
                        </span>
                    @elseif ($transaction->payment_status === 'expired')
                        <span class="badge bg-secondary">
                            Payment Expired
                        </span>
                    @endif
                </div>
            </div>

            <div class="card-body">
                <div class="row g-4">
                    <div class="col-md-6 col-lg-4">
                        <div class="small text-muted">
                            Order Code
                        </div>
                        <div class="fw-semibold">
                            {{ $transaction->order_code }}
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-4">
                        <div class="small text-muted">
                            Invoice Number
                        </div>
                        <div class="fw-semibold">
                            {{ $transaction->invoice_number ?? '-' }}
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-4">
                        <div class="small text-muted">
                            Total Amount
                        </div>

                        <div class="fw-semibold">
                            Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-4">
                        <div class="small text-muted">
                            Buyer
                        </div>
                        <div class="fw-semibold">
                            {{ $transaction->buyer->name ?? '-' }}
                        </div>
                        <div class="small text-muted">
                            {{ $transaction->buyer->email ?? '-' }}
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-4">
                        <div class="small text-muted">
                            Created At
                        </div>
                        <div class="fw-semibold">
                            {{ $transaction->created_at->format('d M Y H:i') }}
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-4">
                        <div class="small text-muted">
                            Paid At
                        </div>
                        <div class="fw-semibold">
                            {{ $transaction->paid_at ? $transaction->paid_at->format('d M Y H:i') : '-' }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-4 mb-3">
                <div class="card h-100 text-bg-light">
                    <div class="card-body">
                        <div class="text-muted small mb-1">
                            Gross Transaction
                        </div>
                        <h5 class="mb-0">
                            Rp{{ number_format($transaction->items->sum('price_snapshot'), 0, ',', '.') }}
                        </h5>
                    </div>
                </div>
            </div>

            <div class="col-md-4 mb-3">
                <div class="card h-100 text-bg-light">
                    <div class="card-body">
                        <div class="text-muted small mb-1">
                            Platform Commission
                        </div>
                        <h5 class="mb-0">
                            Rp{{ number_format($transaction->items->sum('commission_amount'), 0, ',', '.') }}
                        </h5>
                    </div>
                </div>
            </div>

            <div class="col-md-4 mb-3">
                <div class="card h-100 text-bg-light">
                    <div class="card-body">
                        <div class="text-muted small mb-1">
                            Seller Net Amount
                        </div>
                        <h5 class="mb-0">
                            Rp
                            {{ number_format($transaction->items->sum('seller_amount'), 0, ',', '.') }}
                        </h5>
                    </div>
                </div>
            </div>
        </div>

        <div class="admin-table-wrapper">
            <div class="d-flex justify-content-between
                    align-items-center mb-3">
                <div>
                    <h5 class="mb-0">
                        Transaction Items
                    </h5>
                    <small class="text-muted">
                        Financial details are recorded per seller and item.
                    </small>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-striped
                        table-hover align-middle">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Seller</th>
                            <th>Item</th>
                            <th>Type</th>
                            <th>License</th>
                            <th>Price</th>
                            <th>Commission</th>
                            <th>Seller Amount</th>
                            <th>Download</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($transaction->items as $item)
                            <tr>
                                <td>
                                    {{ $loop->iteration }}
                                </td>
                                <td>
                                    <div class="fw-semibold ">
                                        {{ $item->seller->name ?? '-' }}
                                    </div>

                                    <div class="small text-muted">
                                        {{ $item->seller->email ?? '-' }}
                                    </div>
                                </td>

                                <td>
                                    <div class="fw-semibold">
                                        {{ $item->item_name_snapshot }}
                                    </div>
                                </td>

                                <td>
                                    @if ($item->item_type === 'bundle' && $item->folder)
                                        <a href="{{ route('folder.show', $item->folder->id) }}" class="badge bg-dark">
                                            <span class="badge bg-dark">
                                                Bundle
                                            </span>
                                        </a>
                                    @elseif ($item->content)
                                        <a href="{{ route('content.detail', $item->content->id) }}"
                                            class="text-decoration-none">
                                            <span class="badge bg-secondary">
                                                Content
                                            </span>
                                        </a>
                                    @else
                                        <span class="badge bg-info text-dark">
                                            {{ ucwords(str_replace('_', ' ', $item->item_type)) }}
                                        </span>
                                    @endif

                                </td>


                                <td>
                                    {{ $item->license_name_snapshot }}
                                </td>


                                <td class="text-nowrap">

                                    Rp
                                    {{ number_format($item->price_snapshot, 0, ',', '.') }}

                                </td>


                                <td class="text-nowrap">

                                    @if ($item->commission_amount !== null)
                                        Rp
                                        {{ number_format($item->commission_amount, 0, ',', '.') }}
                                    @else
                                        <span class="text-muted">
                                            -
                                        </span>
                                    @endif

                                </td>


                                <td class="text-nowrap">

                                    @if ($item->seller_amount !== null)
                                        Rp
                                        {{ number_format($item->seller_amount, 0, ',', '.') }}
                                    @else
                                        <span class="text-muted">
                                            -
                                        </span>
                                    @endif

                                </td>


                                <td>

                                    @if ($item->final_file_path && $item->download_expires_at && now()->lte($item->download_expires_at))
                                        <span class="badge bg-success">
                                            Available
                                        </span>

                                        <div class="small text-muted mt-1">
                                            {{ $item->download_count }}
                                            download(s)
                                        </div>
                                    @elseif ($item->download_expires_at && now()->gt($item->download_expires_at))
                                        <span class="badge bg-secondary">
                                            Expired
                                        </span>
                                    @else
                                        <span class="badge bg-warning text-dark">
                                            Locked
                                        </span>
                                    @endif

                                </td>

                            </tr>


                        @empty

                            <tr>

                                <td colspan="9" class="text-center py-4 text-muted">

                                    No transaction items found.

                                </td>

                            </tr>
                        @endforelse

                    </tbody>

                </table>

            </div>

        </div>

    </div>
@endsection
