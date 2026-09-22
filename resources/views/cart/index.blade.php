@extends('layouts.main')

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card shadow-sm mb-4">
                <div class="card-body p-4 p-md-5">

                    <div class="mb-4">
                        <h2 class="card-title mb-2">
                            {{ __('Shopping Cart') }}
                        </h2>
                        <p class="card-subtitle text-muted mb-0">
                            {{ __('Review your selected contents and bundles before checkout.') }}
                        </p>
                    </div>

                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert">
                            </button>
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <strong>
                                Action failed:
                            </strong>
                            <ul class="mb-0 mt-2">
                                @foreach ($errors->all() as $error)
                                    <li>
                                        {{ $error }}
                                    </li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert">
                            </button>
                        </div>
                    @endif

                    @if ($items->isEmpty())
                        <div class="text-center py-5">
                            <i class="fi fi-rr-shopping-cart" style="font-size: 48px;">
                            </i>
                            <h5 class="mt-3">
                                Your cart is empty.
                            </h5>
                            <p class="text-muted mb-0">
                                Add content or bundles to begin your purchase.
                            </p>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-striped table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th>Item</th>
                                        <th>Type</th>
                                        <th>Seller</th>
                                        <th>Price</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($items as $item)
                                        @php
                                            $isContent = in_array($item->item_type, ['content', 'content_with_preset']);
                                            $itemName = $isContent
                                                ? $item->content?->content_title
                                                : $item->folder?->folder_name;

                                            $seller = $isContent ? $item->content?->user : $item->folder?->user;
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
                                                @if ($item->preset)
                                                    <div class="small text-muted">
                                                        Preset:
                                                        {{ $item->preset->preset_name }}
                                                    </div>
                                                @endif
                                            </td>

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

                                            <td>
                                                {{ $seller?->name ?? '-' }}
                                            </td>

                                            <td>
                                                <div>
                                                    Rp
                                                    {{ number_format($item->price_snapshot, 0, ',', '.') }}
                                                </div>

                                                @if ($priceChanged[$item->id] ?? false)
                                                    <div class="small text-danger mt-1">
                                                        Current price:
                                                        Rp
                                                        {{ number_format($currentPrices[$item->id], 0, ',', '.') }}
                                                    </div>
                                                @endif
                                            </td>

                                            <td>
                                                @if (isset($availabilityErrors[$item->id]))
                                                    <span class="badge bg-danger">
                                                        Unavailable
                                                    </span>
                                                    <div class="small text-danger mt-1">
                                                        {{ $availabilityErrors[$item->id] }}
                                                    </div>
                                                @elseif ($priceChanged[$item->id] ?? false)
                                                    <span class="badge bg-warning text-dark">
                                                        Price Changed
                                                    </span>
                                                @else
                                                    <span class="badge bg-success">
                                                        Available
                                                    </span>
                                                @endif
                                            </td>

                                            <td>
                                                @if (($priceChanged[$item->id] ?? false) && !isset($availabilityErrors[$item->id]))
                                                    <form method="POST"
                                                        action="{{ route('cart.items.refresh-price', $item) }}"
                                                        class="d-inline">

                                                        @csrf
                                                        @method('PATCH')


                                                        <button type="submit" class="btn btn-sm btn-outline-warning">
                                                            Update Price
                                                        </button>
                                                    </form>
                                                @endif

                                                <form method="POST" action="{{ route('cart.items.destroy', $item) }}"
                                                    class="d-inline">

                                                    @csrf
                                                    @method('DELETE')


                                                    <button type="submit" class="btn btn-sm btn-outline-danger"
                                                        onclick="return confirm('Remove this item from cart?')">
                                                        <i class="fi fi-rr-trash">
                                                        </i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <hr class="my-4">

                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="text-muted">
                                    Total
                                </div>
                                <h4 class="mb-0">
                                    Rp
                                    {{ number_format($subtotal, 0, ',', '.') }}
                                </h4>
                            </div>
                            <div class="text-end">
                                @if ($canCheckout)
                                    <a href="{{ route('checkout.index') }}" class="btn btn-dark btn-lg">
                                        Checkout
                                    </a>
                                @else
                                    <button type="button" class="btn btn-secondary btn-lg" disabled>
                                        Checkout
                                    </button>

                                    <div class="small text-danger mt-1">
                                        Resolve unavailable items
                                        or price changes first.
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
