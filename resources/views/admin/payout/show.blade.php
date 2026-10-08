@extends('layouts.admin')

@section('content')
    <div class="container-fluid px-4">
        <div class="d-flex justify-content-between align-items-center my-4">
            <div>
                <h1 class="m-0">
                    Payout #{{ $payout->id }}
                </h1>

                <p class="text-muted mb-0">
                    Review seller payout request.
                </p>
            </div>

            {{-- <a href="{{ route('admin.payout.index') }}" class="btn btn-outline-secondary">
                Back
            </a> --}}
        </div>
        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif


        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="text-muted small mb-1">
                            Seller
                        </div>

                        <h5>
                            {{ $payout->seller->name }}
                        </h5>

                        <div class="text-muted">
                            {{ $payout->seller->email }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="text-muted small mb-1">
                            Destination Bank
                        </div>

                        <h5>
                            {{ $payout->bankAccount->bank_name }}
                        </h5>

                        <div>
                            {{ $payout->bankAccount->account_name }}
                        </div>

                        <div class="text-muted">
                            {{ $payout->bankAccount->account_number }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="text-muted small mb-1">
                            Payout Amount
                        </div>
                        <h3>
                            Rp {{ number_format($payout->amount, 0, ',', '.') }}
                        </h3>

                        @if ($payout->status !== 'rejected' && !$amountMatches)
                            <div class="alert alert-danger py-2 mb-0">
                                Amount mismatch detected.
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0">
                    Payout Information
                </h5>
            </div>

            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <div class="small text-muted">
                            Status
                        </div>

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
                    </div>

                    <div class="col-md-3">
                        <div class="small text-muted">
                            Requested At
                        </div>
                        {{ $payout->requested_at ? $payout->requested_at->format('d M Y H:i') : '-' }}
                    </div>

                    <div class="col-md-3">
                        <div class="small text-muted">
                            Processed By
                        </div>
                        {{ $payout->processor?->name ?? '-' }}
                    </div>
                    <div class="col-md-3">
                        <div class="small text-muted">
                            Processed At
                        </div>
                        {{ $payout->processed_at ? $payout->processed_at->format('d M Y H:i') : '-' }}
                    </div>
                </div>

                @if ($payout->admin_note)
                    <hr>
                    <div>
                        <div class="small text-muted">
                            Admin Note
                        </div>
                        {{ $payout->admin_note }}
                    </div>
                @endif
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0">
                    Sales Included in Payout
                </h5>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Order</th>
                            <th>Paid At</th>
                            <th>Item</th>
                            <th>Type</th>
                            <th class="text-end">
                                Sale Price
                            </th>
                            <th class="text-end">
                                Commission
                            </th>
                            <th class="text-end">
                                Seller Amount
                            </th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($payout->items as $item)
                            <tr>
                                <td>
                                    {{ $item->transaction->order_code }}
                                </td>
                                <td>
                                    {{ $item->transaction->paid_at ? $item->transaction->paid_at->format('d M Y H:i') : '-' }}
                                </td>
                                <td>
                                    {{ $item->item_name_snapshot }}
                                </td>
                                <td>
                                    {{ ucfirst(str_replace('_', ' ', $item->item_type)) }}
                                </td>
                                <td class="text-end">
                                    Rp
                                    {{ number_format($item->price_snapshot, 0, ',', '.') }}
                                </td>
                                <td class="text-end">
                                    Rp
                                    {{ number_format($item->commission_amount, 0, ',', '.') }}
                                </td>
                                <td class="text-end fw-semibold">
                                    Rp
                                    {{ number_format($item->seller_amount, 0, ',', '.') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>

                    <tfoot>
                        <tr>
                            <th colspan="6" class="text-end">
                                Calculated Seller Amount
                            </th>
                            <th class="text-end">
                                Rp
                                {{ number_format($calculatedAmount, 0, ',', '.') }}
                            </th>
                        </tr>
                    </tfoot>
                </table>

            </div>
        </div>

        @if ($payout->status === 'pending')
            <div class="alert alert-warning">
                <strong>Important:</strong>
                Mark the payout as paid only after the transfer
                has actually been completed to the seller's
                bank account.
            </div>

            <div class="row g-4 mb-5">
                <div class="col-md-6">
                    <div class="card border-success h-100">
                        <div class="card-body">
                            <h5>
                                Mark as Paid
                            </h5>

                            <p class="text-muted">
                                Use this after completing the
                                manual bank transfer.
                            </p>

                            <form method="POST" action="{{ route('admin.payout.paid', $payout) }}"
                                onsubmit="return confirm(
                                    'Confirm that the bank transfer has been completed?'
                                )">
                                @csrf
                                @method('PATCH')

                                <div class="mb-3">
                                    <label class="form-label">
                                        Transfer Note
                                    </label>

                                    <textarea name="admin_note" class="form-control" rows="3" placeholder="Optional transfer reference or note">{{ old('admin_note') }}</textarea>
                                </div>
                                <button type="submit" class="btn btn-success">
                                    Mark as Paid
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card border-danger h-100">
                        <div class="card-body">
                            <h5>
                                Reject Payout
                            </h5>
                            <p class="text-muted">
                                Rejected earnings will become
                                available to the seller again.
                            </p>
                            <form method="POST" action="{{ route('admin.payout.reject', $payout) }}"
                                onsubmit="return confirm(
                                    'Reject this payout request?'
                                )">
                                @csrf
                                @method('PATCH')

                                <div class="mb-3">
                                    <label class="form-label">
                                        Rejection Reason
                                    </label>

                                    <textarea name="admin_note" class="form-control" rows="3" required
                                        placeholder="Explain why this payout is rejected">{{ old('admin_note') }}</textarea>
                                </div>

                                <button type="submit" class="btn btn-danger">
                                    Reject Payout
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection
