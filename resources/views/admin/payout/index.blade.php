@extends('layouts.admin')

@section('content')
    <div class="container-fluid px-4 admin-page-wrapper">
        <div class="d-flex justify-content-between align-items-center my-4 admin-page-title">
            <div>
                <h1 class="m-0">Payouts</h1>
                <p class="text-muted mb-0">
                    Review and process seller payout requests.
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

        <div class="admin-table-wrapper">
            @include('admin.partials.filter-bar', [
                'action' => route('admin.payout.index'),
                'filters' => [['name' => 'status', 'label' => 'Status', 'options' => App\Models\Payout::STATUSES]],
            ])

            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead>
                        <tr>
                            <th scope="col">No</th>
                            <th scope="col">ID</th>
                            <th scope="col">Seller</th>
                            <th scope="col">Bank Account</th>
                            <th scope="col">Items</th>
                            <th scope="col">Amount</th>
                            <th scope="col">Requested At</th>
                            <th scope="col">Status</th>
                            <th scope="col">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($payouts as $payout)
                            <tr>
                                <td>{{ $payouts->firstItem() + $loop->index }}</td>
                                <td class="fw-semibold">#{{ $payout->id }}</td>
                                <td>
                                    <div class="fw-semibold">
                                        {{ $payout->seller->name ?? '-' }}
                                    </div>
                                    <div class="small text-muted">
                                        {{ $payout->seller->email ?? '-' }}
                                    </div>
                                </td>
                                <td>
                                    <div>{{ $payout->bankAccount->bank_name ?? '-' }}</div>
                                    <div class="small text-muted">
                                        {{ $payout->bankAccount->account_number ?? '-' }}
                                    </div>
                                </td>
                                <td>{{ $payout->items_count }}</td>
                                <td class="text-nowrap">
                                    Rp{{ number_format($payout->amount, 0, ',', '.') }}
                                </td>
                                <td class="text-nowrap">
                                    {{ $payout->requested_at ? $payout->requested_at->format('d M Y H:i') : '-' }}
                                </td>
                                <td>
                                    @if ($payout->status === 'pending')
                                        <span class="badge bg-warning text-dark">Pending</span>
                                    @elseif ($payout->status === 'paid')
                                        <span class="badge bg-success">Paid</span>
                                    @else
                                        <span class="badge bg-danger">Rejected</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('admin.payout.show', $payout) }}"
                                        class="btn btn-sm btn-outline-primary" title="View Detail">
                                        <i class="fi fi-rr-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-4 text-muted">
                                    No payout requests found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                {{ $payouts->links() }}
            </div>
        </div>
    </div>
@endsection
