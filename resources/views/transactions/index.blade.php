@extends('layouts.main')

@section('content')
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="mb-4">

                    <h2 class="fw-bold mb-1">
                        My Transactions
                    </h2>

                    <p class="text-muted mb-0">
                        View your checkout and payment history.
                    </p>

                </div>

                @if ($transactions->isEmpty())
                    <div class="card shadow-sm">
                        <div class="card-body text-center py-5">
                            <i class="fi fi-rr-receipt" style="font-size: 48px;">
                            </i>
                            <h5 class="mt-3">
                                No transactions yet
                            </h5>
                            <p class="text-muted mb-0">
                                Your completed checkouts will appear here.
                            </p>
                        </div>
                    </div>
                @else
                    <div class="card shadow-sm">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Order</th>
                                        <th>Date</th>
                                        <th>Items</th>
                                        <th>Total</th>
                                        <th>Transaction</th>
                                        <th>Payment</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($transactions as $transaction)
                                        <tr>
                                            <td>
                                                <span class="fw-semibold">
                                                    {{ $transaction->order_code }}
                                                </span>
                                            </td>
                                            <td>
                                                {{ $transaction->created_at->format('d M Y H:i') }}
                                            </td>
                                            <td>
                                                {{ $transaction->items_count }}
                                            </td>
                                            <td>
                                                Rp
                                                {{ number_format($transaction->total_amount, 0, ',', '.') }}
                                            </td>
                                            <td>
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
                                            </td>
                                            <td>
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
                                            </td>

                                            <td class="text-end">
                                                <a href="{{ route('transactions.show', $transaction) }}"
                                                    class="btn btn-sm btn-outline-dark">
                                                    View
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="mt-4">
                        {{ $transactions->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
