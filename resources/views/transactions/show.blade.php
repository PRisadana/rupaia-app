@extends('layouts.main')

@section('content')
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow-sm">
                    <div class="card-body p-4 p-md-5">
                        @if ($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <strong>There was a problem:</strong>
                                <ul class="mb-0 mt-2">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        @if (session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif


                        <h2 class="mb-4">
                            Order Created
                        </h2>

                        <div class="mb-3">
                            <div class="text-muted">
                                Order Code
                            </div>
                            <strong>
                                {{ $transaction->order_code }}
                            </strong>
                        </div>

                        <div class="mb-3">
                            @if ($transaction->invoice_number)
                                <div class="text-muted">
                                    Invoice
                                </div>
                                <strong>
                                    {{ $transaction->invoice_number }}
                                </strong>
                            @endif
                        </div>

                        <div class="mb-3">
                            <div class="text-muted">
                                Transaction Status
                            </div>
                            @switch($transaction->status)
                                @case('completed')
                                    <span class="badge bg-success">
                                        Completed
                                    </span>
                                @break

                                @case('cancelled')
                                    <span class="badge bg-secondary">
                                        Cancelled
                                    </span>
                                @break

                                @default
                                    <span class="badge bg-warning text-dark">
                                        Pending
                                    </span>
                            @endswitch
                        </div>

                        <div class="mb-4">
                            <div class="text-muted">
                                Payment Status
                            </div>
                            @switch($transaction->payment_status)
                                @case('success')
                                    <span class="badge bg-success">
                                        Success
                                    </span>
                                @break

                                @case('failed')
                                    <span class="badge bg-danger">
                                        Failed
                                    </span>
                                @break

                                @case('expired')
                                    <span class="badge bg-secondary">
                                        Expired
                                    </span>
                                @break

                                @default
                                    <span class="badge bg-warning text-dark">
                                        Pending
                                    </span>
                            @endswitch
                        </div>

                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Item</th>
                                        <th>Item Type</th>
                                        <th>License</th>
                                        <th>Price</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($transaction->items as $item)
                                        <tr>
                                            <td>{{ $item->item_name_snapshot }}</td>
                                            <td>
                                                @if ($item->item_type === 'bundle')
                                                    <span class="badge bg-info text-dark">
                                                        Bundle
                                                    </span>
                                                @elseif ($item->item_type === 'content_with_preset')
                                                    <span class="badge bg-primary">
                                                        Content + Preset
                                                    </span>
                                                @else
                                                    <span class="badge bg-secondary">
                                                        Content
                                                    </span>
                                                @endif
                                            </td>
                                            <td>{{ $item->license_name_snapshot }}</td>
                                            <td>
                                                Rp{{ number_format($item->price_snapshot, 0, ',', '.') }}
                                            </td>
                                            <td>
                                                @if ($transaction->status === 'completed' && $transaction->payment_status === 'success' && $item->final_file_path)
                                                    @if ($item->download_expires_at && now()->lte($item->download_expires_at))
                                                        <a href="{{ route('transactions.items.download', [
                                                            'transaction' => $transaction,
                                                            'transactionItem' => $item,
                                                        ]) }}"
                                                            class="btn btn-sm btn-dark">
                                                            Download
                                                        </a>
                                                        <div class="small text-muted mt-1">
                                                            Available until
                                                            {{ $item->download_expires_at->format('d M Y H:i') }}
                                                        </div>
                                                    @else
                                                        <span class="badge bg-secondary">
                                                            Download expired
                                                        </span>
                                                    @endif
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <hr>

                        <div class="d-flex justify-content-between align-items-center">
                            <strong>
                                Total
                            </strong>
                            <h4 class="mb-0">
                                Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}
                            </h4>
                        </div>

                        <div class="mt-4">
                            @if ($transaction->status === 'pending' && $transaction->payment_status === 'pending')
                                @if (!$transaction->snap_token)
                                    <form method="POST" action="{{ route('transactions.payment.create', $transaction) }}">
                                        @csrf
                                        <button type="submit" class="btn btn-dark">
                                            <i class="fi fi-rr-credit-card me-1"></i>
                                            Pay Now
                                        </button>
                                    </form>
                                @else
                                    <button type="button" id="pay-button" class="btn btn-dark">
                                        <i class="fi fi-rr-credit-card me-1"></i>
                                        Continue Payment
                                    </button>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if ($transaction->snap_token && $transaction->status === 'pending' && $transaction->payment_status === 'pending')
        <script type="text/javascript" src="https://app.sandbox.midtrans.com/snap/snap.js"
            data-client-key="{{ config('midtrans.client_key') }}"></script>
    @endif

    @if ($transaction->snap_token && $transaction->status === 'pending' && $transaction->payment_status === 'pending')
        <script>
            document.addEventListener(
                'DOMContentLoaded',
                function() {
                    const payButton =
                        document.getElementById(
                            'pay-button'
                        );
                    if (payButton) {
                        payButton.addEventListener(
                            'click',
                            function() {
                                window.snap.pay(
                                    @json($transaction->snap_token)
                                );

                            }
                        );
                    }
                }
            );
        </script>
    @endif

    @if (session('open_midtrans') && $transaction->snap_token)
        <script>
            document.addEventListener(
                'DOMContentLoaded',
                function() {
                    window.snap.pay(
                        @json($transaction->snap_token)
                    );
                }
            );
        </script>
    @endif
@endsection
