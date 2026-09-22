@extends('layouts.main')

@section('content')
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card shadow-sm">
                    <div class="card-body p-4 p-md-5">
                        <h2 class="mb-2">
                            Checkout
                        </h2>
                        <p class="text-muted mb-4">
                            Review your order before creating the transaction.
                        </p>

                        <div class="table-responsive">
                            <table class="table align-middle">
                                <thead>
                                    <tr>
                                        <th>Item</th>
                                        <th>Type</th>
                                        <th>Seller</th>
                                        <th>License</th>
                                        <th class="text-end">
                                            Price
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($cart->items as $item)
                                        @php
                                            $isContent = in_array($item->item_type, ['content', 'content_with_preset']);
                                            $product = $isContent ? $item->content : $item->folder;
                                            $name = $isContent
                                                ? $item->content->content_title
                                                : $item->folder->folder_name;
                                            $seller = $isContent ? $item->content->user : $item->folder->user;
                                            $license = $isContent ? $item->content->license : $item->folder->license;
                                        @endphp
                                        <tr>
                                            <td>
                                                <div class="fw-semibold">
                                                    @if ($item->item_type === 'bundle' && $item->folder)
                                                        <a href="{{ route('folder.show', $item->folder->id) }}"
                                                            class="text-decoration-none">
                                                            {{ $item->folder->folder_name ?? 'Unavailable Bundle' }}
                                                        </a>
                                                    @elseif ($item->content)
                                                        <a href="{{ route('content.detail', $item->content->id) }}"
                                                            class="text-decoration-none">
                                                            {{ $item->content->content_title ?? 'Unavailable Item' }}
                                                        </a>
                                                    @else
                                                        <span class="text-muted">Unavailable Item</span>
                                                    @endif
                                                </div>
                                            </td>
                                            <td>
                                                {{ ucfirst(str_replace('_', ' ', $item->item_type)) }}
                                            </td>
                                            <td>
                                                {{ $seller->name }}
                                            </td>
                                            <td>
                                                {{ $license->name }}
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
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <strong>
                                Total
                            </strong>
                            <h4 class="mb-0">
                                Rp
                                {{ number_format($total, 0, ',', '.') }}
                            </h4>
                        </div>

                        <div class="d-flex gap-2 justify-content-end">
                            <a href="{{ route('cart.index') }}" class="btn btn-outline-secondary">
                                Back to Cart
                            </a>
                            <form method="POST" action="{{ route('checkout.store') }}">
                                @csrf
                                <button type="submit" class="btn btn-dark">
                                    Confirm Checkout
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
