@extends('layouts.admin')

@section('content')
    <div class="container-fluid px-4">

        <div class="d-flex justify-content-between align-items-center my-4">
            <div>
                <h1 class="m-0">Payouts</h1>
                <p class="text-muted mb-0">
                    Review and process seller payout requests.
                </p>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        {{-- @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif --}}

        @include('admin.partials.filter-bar', [
            'action' => route('admin.payout.index'),
            'filters' => [['name' => 'status', 'label' => 'Status', 'options' => App\Models\Payout::STATUSES]],
        ])

        <div class="card">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Seller</th>
                            <th>Bank Account</th>
                            <th>Items</th>
                            <th class="text-end">
                                Amount
                            </th>
                            <th>Requested At</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($payouts as $payout)
                            <tr>
                                <td>
                                    #{{ $payout->id }}
                                </td>
                                <td>
                                    <strong>
                                        {{ $payout->seller->name }}
                                    </strong>
                                    <div class="small text-muted">
                                        {{ $payout->seller->email }}
                                    </div>
                                </td>
                                <td>
                                    {{ $payout->bankAccount->bank_name }}
                                    <div class="small text-muted">
                                        {{ $payout->bankAccount->account_number }}
                                    </div>
                                </td>
                                <td>
                                    {{ $payout->items_count }}
                                </td>
                                <td class="text-end fw-semibold">
                                    Rp
                                    {{ number_format($payout->amount, 0, ',', '.') }}
                                </td>
                                <td>
                                    {{ $payout->requested_at ? $payout->requested_at->format('d M Y H:i') : '-' }}
                                </td>
                                <td>
                                    @if ($payout->status === 'pending')
                                        <span class="badge bg-warning text-dark">
                                            Pending
                                        </span>
                                    @elseif ($payout->status === 'paid')
                                        <span class="badge bg-success">
                                            Paid
                                        </span>
                                    @else
                                        <span class="badge bg-danger">
                                            Rejected
                                        </span>
                                    @endif
                                </td>

                                <td>
                                    <a href="{{ route('admin.payout.show', $payout) }}" class="btn btn-sm btn-outline-dark">
                                        View Detail
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-5">
                                    No payout requests found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($payouts->hasPages())
                <div class="card-footer bg-white">
                    {{ $payouts->links() }}
                </div>
            @endif
        </div>

    </div>
@endsection
