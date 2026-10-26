@extends('layouts.main')

@section('content')
    <div class="container my-4">
        <div class="p-4 bg-body-tertiary rounded-3 mb-4">
            <h1 class="h3 mb-2">Ratings</h1>
            <p class="text-muted mb-0">
                See how buyers rate your contents and bundles.
            </p>
        </div>

        @include('components.seller-nav', ['active' => 'rating'])

        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-body">
                        <p class="text-muted mb-2">Overall Rating</p>
                        @if ($overview['count'] === 0)
                            <h3 class="mb-0">No ratings yet</h3>
                        @else
                            @php $rounded = (int) round($overview['average']); @endphp
                            <div class="d-flex align-items-center gap-2">
                                <h3 class="mb-0">{{ number_format($overview['average'], 1) }}</h3>
                                <span class="text-warning fs-5" aria-hidden="true">
                                    {{ str_repeat('★', $rounded) }}{{ str_repeat('☆', 5 - $rounded) }}
                                </span>
                                <span class="text-muted">
                                    ({{ $overview['count'] }}
                                    {{ \Illuminate\Support\Str::plural('rating', $overview['count']) }})
                                </span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-body">
                        <p class="text-muted mb-2">Rated Products</p>
                        <h3 class="mb-0">{{ $overview['products']->count() }}</h3>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0">Rating by Product</h5>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Product</th>
                            <th>Type</th>
                            <th>Rating</th>
                            <th class="text-end">Ratings</th>
                            <th class="text-end">Hidden by Admin</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($overview['products'] as $product)
                            <tr>
                                <td>
                                    <div class="fw-semibold">
                                        @if ($product['type'] === 'bundle')
                                            <a href="{{ route('folder.show', $product['id']) }}"
                                                class="text-decoration-none">
                                                {{ $product['name'] ?? 'Unavailable Bundle' }}
                                            </a>
                                        @elseif ($product['type'] === 'content')
                                            <a href="{{ route('content.detail', $product['id']) }}"
                                                class="text-decoration-none">
                                                {{ $product['name'] ?? 'Unavailable Item' }}
                                            </a>
                                        @else
                                            <span class="text-muted">Unavailable Item</span>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <span class="badge {{ $product['type'] === 'bundle' ? 'bg-dark' : 'bg-secondary' }}">
                                        {{ ucfirst($product['type']) }}
                                    </span>
                                </td>
                                <td>
                                    @if ($product['average'] === null)
                                        <span class="text-muted">No ratings yet</span>
                                    @else
                                        <span class="fw-semibold">{{ number_format($product['average'], 1) }}</span>
                                        <span class="text-warning" aria-hidden="true">
                                            @php $r = (int) round($product['average']); @endphp
                                            {{ str_repeat('★', $r) }}{{ str_repeat('☆', 5 - $r) }}
                                        </span>
                                    @endif
                                </td>
                                <td class="text-end">{{ $product['count'] }}</td>
                                <td class="text-end">{{ $product['hidden'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-5">
                                    No ratings yet. Ratings appear after buyers rate their completed purchases.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0">Recent Reviews</h5>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Product</th>
                            <th>Buyer</th>
                            <th>Rating</th>
                            <th>Comment</th>
                            <th>Updated</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($reviews as $review)
                            @php
                                $isHidden = $review->status === 'hidden';
                            @endphp
                            <tr>
                                <td>
                                    <div class="fw-semibold">
                                        @if ($review->transactionItem->folder_id)
                                            <a href="{{ route('folder.show', $review->transactionItem->folder_id) }}"
                                                class="text-decoration-none">
                                                {{ $review->transactionItem->folder->folder_name ?? 'Unavailable Bundle' }}
                                            </a>
                                        @elseif ($review->transactionItem->content_id)
                                            <a href="{{ route('content.detail', $review->transactionItem->content_id) }}"
                                                class="text-decoration-none">
                                                {{ $review->transactionItem->content->content_title ?? 'Unavailable Item' }}
                                            </a>
                                        @else
                                            <span class="text-muted">Unavailable Item</span>
                                        @endif
                                    </div>
                                </td>
                                <td>{{ $review->reviewerName() }}</td>
                                <td class="text-warning text-nowrap" aria-label="{{ $review->rating_value }} out of 5">
                                    {{ str_repeat('★', $review->rating_value) }}{{ str_repeat('☆', 5 - $review->rating_value) }}
                                </td>
                                <td class="text-break" style="max-width: 320px;">
                                    @if ($isHidden)
                                        <span class="badge bg-secondary">Hidden by admin</span>
                                    @else
                                        {{ $review->comment ?: '-' }}
                                    @endif
                                </td>
                                <td class="text-nowrap">{{ $review->updated_at->format('d M Y H:i') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-5">No reviews yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($reviews->hasPages())
                <div class="card-footer bg-white">
                    {{ $reviews->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
