@extends('layouts.admin')

@section('content')
    <div class="container-fluid px-4 admin-page-wrapper">
        <div class="d-flex justify-content-between align-items-center my-4 admin-page-title">
            <div>
                <h1 class="m-0">Transactions</h1>
                <p class="text-muted mb-0">
                    Monitor buyer transactions and payment status.
                </p>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
                </button>
            </div>
        @endif

        {{-- @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
                </button>
            </div>
        @endif --}}

        <div class="admin-table-wrapper">
            @include('admin.partials.filter-bar', [
                'action' => route('admin.transaction.index'),
                'searchPlaceholder' => 'Search order code, invoice, buyer name, or email',
                'filters' => [
                    ['name' => 'status', 'label' => 'Status', 'options' => App\Models\Transaction::STATUSES],
                    [
                        'name' => 'payment_status',
                        'label' => 'Payment',
                        'options' => App\Models\Transaction::PAYMENT_STATUSES,
                    ],
                ],
            ])

            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead>
                        <tr>
                            <th scope="col">No</th>
                            <th scope="col">Order Code</th>
                            <th scope="col">Buyer</th>
                            <th scope="col">Total</th>
                            <th scope="col">Items</th>
                            <th scope="col">Transaction</th>
                            <th scope="col">Payment</th>
                            <th scope="col">Created At</th>
                            <th scope="col">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($transactions as $transaction)
                            <tr>
                                <td>{{ $transactions->firstItem() + $loop->index }}</td>
                                <td class="fw-semibold">{{ $transaction->order_code }}</td>
                                <td>
                                    <div class="fw-semibold">
                                        {{ $transaction->buyer->name ?? '-' }}
                                    </div>
                                    <div class="small text-muted">
                                        {{ $transaction->buyer->email ?? '-' }}
                                    </div>
                                </td>
                                <td class="text-nowrap">
                                    Rp{{ number_format($transaction->total_amount, 0, ',', '.') }}
                                </td>
                                <td>{{ $transaction->items_count }}</td>
                                <td>
                                    @if ($transaction->status === 'completed')
                                        <span class="badge bg-success">
                                            Completed
                                        </span>
                                    @elseif ($transaction->status === 'pending')
                                        <span class="badge bg-warning text-dark">
                                            Pending
                                        </span>
                                    @elseif ($transaction->status === 'cancelled')
                                        <span class="badge bg-secondary">
                                            Cancelled
                                        </span>
                                    @elseif ($transaction->status === 'processing')
                                        <span class="badge bg-info text-dark">
                                            Processing
                                        </span>
                                    @else
                                        <span class="badge bg-secondary">
                                            {{ ucfirst($transaction->status) }}
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    @if ($transaction->payment_status === 'success')
                                        <span class="badge bg-success">
                                            Success
                                        </span>
                                    @elseif ($transaction->payment_status === 'pending')
                                        <span class="badge bg-warning text-dark">
                                            Pending
                                        </span>
                                    @elseif ($transaction->payment_status === 'failed')
                                        <span class="badge bg-danger">
                                            Failed
                                        </span>
                                    @elseif ($transaction->payment_status === 'expired')
                                        <span class="badge bg-secondary">
                                            Expired
                                        </span>
                                    @else
                                        <span class="badge bg-secondary">
                                            {{ ucfirst($transaction->payment_status) }}
                                        </span>
                                    @endif
                                </td>
                                <td class="text-nowrap">{{ $transaction->created_at->format('d M Y H:i') }}</td>
                                <td>
                                    <a href="{{ route('admin.transaction.show', $transaction) }}"
                                        class="btn btn-sm btn-outline-primary" title="View Detail">
                                        <i class="fi fi-rr-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-4 text-muted">
                                    No transactions found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                {{ $transactions->links() }}
            </div>
        </div>
    </div>
@endsection
