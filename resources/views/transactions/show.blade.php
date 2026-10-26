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

                                @case('processing')
                                    <span class="badge bg-info text-dark">
                                        Processing
                                    </span>
                                @break

                                @default
                                    <span class="badge bg-warning text-dark">
                                        Pending
                                    </span>
                            @endswitch

                            @if ($transaction->status === 'processing')
                                <div class="small text-muted mt-1">
                                    Your payment was received. Your files are being prepared.
                                    This page refreshes automatically.
                                </div>
                                <script>
                                    setTimeout(function() {
                                        window.location.reload();
                                    }, 5000);
                                </script>
                            @endif
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

                        @php
                            $canRate =
                                $transaction->status === 'completed' && $transaction->payment_status === 'success';
                        @endphp

                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Seller</th>
                                        <th>Item</th>
                                        <th>Item Type</th>
                                        <th>License</th>
                                        <th>Price</th>
                                        <th>Action</th>
                                        <th>Rating</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($transaction->items as $item)
                                        <tr>
                                            <td>{{ $item->seller->name }}</td>
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
                                            <td>
                                                @if ($canRate)
                                                    @php
                                                        $rating = $item->rating;
                                                    @endphp

                                                    @if ($rating && $rating->status === 'hidden')
                                                        <span class="badge bg-secondary">Hidden by admin</span>
                                                    @else
                                                        @if ($rating)
                                                            <div class="text-warning" aria-hidden="true">
                                                                {{ str_repeat('★', $rating->rating_value) }}{{ str_repeat('☆', 5 - $rating->rating_value) }}
                                                            </div>
                                                            @if ($rating->comment)
                                                                <div class="small text-muted text-break">
                                                                    {{ \Illuminate\Support\Str::limit($rating->comment, 60) }}
                                                                </div>
                                                            @endif
                                                        @endif

                                                        <button type="button" class="btn btn-sm btn-outline-dark mt-1"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#ratingModal-{{ $item->id }}">
                                                            {{ $rating ? 'Edit rating' : 'Rate' }}
                                                        </button>
                                                    @endif
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @if ($canRate)
                            @foreach ($transaction->items as $item)
                                @php $rating = $item->rating; @endphp
                                @continue($rating && $rating->status === 'hidden')

                                <div class="modal fade" id="ratingModal-{{ $item->id }}" tabindex="-1"
                                    aria-labelledby="ratingModalLabel-{{ $item->id }}" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <form method="POST"
                                            action="{{ route('transactions.items.rating.store', ['transaction' => $transaction, 'transactionItem' => $item]) }}"
                                            class="modal-content">
                                            @csrf

                                            <div class="modal-header">
                                                <h5 class="modal-title" id="ratingModalLabel-{{ $item->id }}">
                                                    Rate: {{ $item->item_name_snapshot }}
                                                </h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                    aria-label="Close"></button>
                                            </div>

                                            <div class="modal-body">
                                                <label class="form-label d-block">Your rating</label>
                                                <div class="btn-group mb-3" role="group" aria-label="Rating">
                                                    @for ($star = 1; $star <= 5; $star++)
                                                        <input type="radio" class="btn-check" name="rating_value"
                                                            id="star-{{ $item->id }}-{{ $star }}"
                                                            value="{{ $star }}" required
                                                            @checked($rating?->rating_value === $star)>
                                                        <label class="btn btn-outline-warning"
                                                            for="star-{{ $item->id }}-{{ $star }}">
                                                            {{ $star }} ★
                                                        </label>
                                                    @endfor
                                                </div>

                                                <label for="comment-{{ $item->id }}" class="form-label">
                                                    Comment (optional)
                                                </label>
                                                <textarea name="comment" id="comment-{{ $item->id }}" class="form-control" rows="4" maxlength="1000"
                                                    placeholder="Share your experience with this item.">{{ $rating?->comment }}</textarea>

                                                <div class="form-check mt-3">
                                                    <input class="form-check-input" type="checkbox" name="is_name_masked"
                                                        id="mask-{{ $item->id }}" value="1"
                                                        @checked($rating ? $rating->is_name_masked : true)>
                                                    <label class="form-check-label" for="mask-{{ $item->id }}">
                                                        Hide half of my name in this review
                                                    </label>
                                                    <div class="form-text">
                                                        Example: "Resandy Prisadana" appears as "Resa*** Prisa****".
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-outline-secondary"
                                                    data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-dark">Save Rating</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        @endif

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
                                    <form method="POST"
                                        action="{{ route('transactions.payment.create', $transaction) }}">
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
