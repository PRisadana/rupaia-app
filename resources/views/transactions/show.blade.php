@extends('layouts.main')

@section('content')
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow-sm">
                    <div class="card-body p-4 p-md-5">
                        @if (session('success'))
                            <div class="alert alert-success">

                                {{ session('success') }}

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
                            <div class="text-muted">
                                Transaction Status
                            </div>
                            <span class="badge bg-warning text-dark">
                                {{ ucfirst($transaction->status) }}
                            </span>
                        </div>

                        <div class="mb-4">
                            <div class="text-muted">
                                Payment Status
                            </div>
                            <span class="badge bg-warning text-dark">
                                {{ ucfirst($transaction->payment_status) }}
                            </span>
                        </div>

                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Item</th>
                                        <th>License</th>
                                        <th class="text-end">
                                            Price
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($transaction->items as $item)
                                        <tr>
                                            <td>
                                                {{ $item->item_name_snapshot }}
                                            </td>
                                            <td>
                                                {{ $item->license_name_snapshot }}
                                            </td>
                                            <td class="text-end">
                                                Rp
                                                {{ number_format($item->price_snapshot, 0, ',', '.') }}
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
                                Rp
                                {{ number_format($transaction->total_amount, 0, ',', '.') }}
                            </h4>
                        </div>

                        <div class="mt-4">
                            <button class="btn btn-dark" disabled>
                                Proceed to Payment
                            </button>

                            <p class="small text-muted mt-2 mb-0">
                                Midtrans payment will be connected in the next stage.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
