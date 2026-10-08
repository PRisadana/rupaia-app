@extends('layouts.main')

@section('content')
    <div class="container my-4">

        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger">
                <strong>Something went wrong:</strong>

                <ul class="mb-0 mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="p-4 bg-body-tertiary rounded-3 mb-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-2">
                        Payout
                    </h1>

                    <p class="text-muted mb-0">
                        Select completed sales to withdraw your earnings.
                    </p>
                </div>

                <div class="text-end">
                    <small class="text-muted d-block">
                        Available Balance
                    </small>

                    <strong class="fs-4">
                        Rp{{ number_format($availableBalance, 0, ',', '.') }}
                    </strong>
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('seller.payout.store') }}">
            @csrf
            <div class="card mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">
                        Destination Bank Account
                    </h5>
                </div>

                <div class="card-body">
                    @if ($bankAccounts->isEmpty())
                        <div class="alert alert-warning mb-0">
                            You need to add a bank account before
                            requesting a payout.
                            <a href="{{ route('bank-accounts.create') }}" class="alert-link">
                                Add Bank Account
                            </a>
                        </div>
                    @else
                        <select name="bank_account_id" class="form-select" required>
                            <option value="">
                                Select bank account
                            </option>
                            @foreach ($bankAccounts as $account)
                                <option value="{{ $account->id }}" @selected(old('bank_account_id') == $account->id || (!old('bank_account_id') && $account->is_default))>
                                    {{ $account->bank_name }}
                                    -
                                    {{ $account->account_number }}
                                    -
                                    {{ $account->account_name }}
                                    @if ($account->is_default)
                                        (Default)
                                    @endif
                                </option>
                            @endforeach
                        </select>
                    @endif
                </div>
            </div>

            <div class="card mb-4">
                <div
                    class="card-header bg-white py-3
                           d-flex justify-content-between
                           align-items-center">
                    <div>
                        <h5 class="mb-1">
                            Available Sales
                        </h5>

                        <small class="text-muted">
                            Select one or more transactions.
                        </small>
                    </div>

                    @if ($availableTransactions->isNotEmpty())
                        <div>
                            <input type="checkbox" class="form-check-input" id="select-all">
                            <label for="select-all" class="form-check-label">
                                Select All
                            </label>
                        </div>
                    @endif
                </div>

                <div class="table-responsive">
                    <table class="table table-hover
                               align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 50px;"></th>
                                <th>Order</th>
                                <th>Sold At</th>
                                <th>Your Items</th>
                                <th class="text-end">
                                    Your Revenue
                                </th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($availableTransactions as $transaction)
                                @php
                                    $transactionRevenue = $transaction->items->sum('seller_amount');
                                @endphp

                                <tr>
                                    <td>
                                        <input type="checkbox" class="form-check-input payout-item" name="transaction_ids[]"
                                            value="{{ $transaction->id }}" data-amount="{{ $transactionRevenue }}">
                                    </td>
                                    <td>
                                        <strong>
                                            {{ $transaction->order_code }}
                                        </strong>
                                    </td>
                                    <td>
                                        {{ $transaction->paid_at ? $transaction->paid_at->format('d M Y H:i') : '-' }}
                                    </td>
                                    <td>
                                        @foreach ($transaction->items as $item)
                                            <div>
                                                {{ $item->item_name_snapshot }}
                                                <span class="text-muted small">
                                                    —
                                                    Rp
                                                    {{ number_format($item->seller_amount, 0, ',', '.') }}
                                                </span>
                                            </div>
                                        @endforeach
                                    </td>
                                    <td class="text-end fw-semibold">
                                        Rp
                                        {{ number_format($transactionRevenue, 0, ',', '.') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5"
                                        class="text-center
                                               text-muted py-5">
                                        No earnings are currently
                                        available for payout.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($availableTransactions->hasPages())
                    <div class="card-footer bg-white">
                        {{ $availableTransactions->links() }}
                    </div>
                @endif
            </div>

            @if ($bankAccounts->isNotEmpty() && $availableTransactions->isNotEmpty())
                <div
                    class="d-flex
                           justify-content-between
                           align-items-center
                           mb-5">
                    <div>
                        <span class="text-muted">
                            Selected payout:
                        </span>

                        <strong class="ms-2 fs-5" id="selected-total">
                            Rp 0
                        </strong>
                    </div>

                    <button type="submit" class="btn btn-dark" id="payout-button" disabled>
                        Request Payout
                    </button>
                </div>
            @endif
        </form>

        <div class="card">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0">
                    Payout History
                </h5>
            </div>

            <div class="table-responsive">
                <table class="table table-hover
                           align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Requested At</th>
                            <th>Bank Account</th>
                            <th class="text-end">Amount</th>
                            <th>Status</th>
                            <th>Admin Note</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($payouts as $payout)
                            <tr>
                                <td>
                                    {{ $payout->requested_at ? $payout->requested_at->format('d M Y H:i') : '-' }}
                                </td>
                                <td>
                                    {{ $payout->bankAccount->bank_name }}
                                    <br>
                                    <small class="text-muted">
                                        {{ $payout->bankAccount->account_number }}
                                    </small>
                                </td>
                                <td class="text-end fw-semibold">
                                    Rp
                                    {{ number_format($payout->amount, 0, ',', '.') }}
                                </td>
                                <td>
                                    @if ($payout->status === 'pending')
                                        <span
                                            class="badge bg-warning
                                                   text-dark">
                                            Pending
                                        </span>
                                    @elseif ($payout->status === 'paid')
                                        <span class="badge bg-success">
                                            Paid
                                        </span>
                                    @elseif ($payout->status === 'rejected')
                                        <span class="badge bg-danger">
                                            Rejected
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    {{ $payout->admin_note ?? '-' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5"
                                    class="text-center
                                           text-muted py-5">
                                    No payout requests yet.
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

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const items =
                document.querySelectorAll('.payout-item');

            const selectAll =
                document.getElementById('select-all');

            const totalElement =
                document.getElementById('selected-total');

            const button =
                document.getElementById('payout-button');

            function updateTotal() {
                let total = 0;
                let selected = 0;

                items.forEach(function(item) {
                    if (item.checked) {
                        total += Number(
                            item.dataset.amount || 0
                        );

                        selected++;
                    }
                });

                if (totalElement) {
                    totalElement.textContent =
                        'Rp ' +
                        new Intl.NumberFormat('id-ID')
                        .format(total);
                }

                if (button) {
                    button.disabled = selected === 0;
                }

                if (selectAll) {
                    selectAll.checked =
                        items.length > 0 &&
                        selected === items.length;
                }
            }

            items.forEach(function(item) {
                item.addEventListener(
                    'change',
                    updateTotal
                );
            });

            if (selectAll) {
                selectAll.addEventListener(
                    'change',
                    function() {
                        items.forEach(
                            function(item) {
                                item.checked =
                                    selectAll.checked;
                            }
                        );

                        updateTotal();
                    }
                );
            }
        });
    </script>
@endsection
