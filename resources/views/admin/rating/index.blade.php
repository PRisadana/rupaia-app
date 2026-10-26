@extends('layouts.admin')

@section('content')
    <div class="container-fluid px-4 admin-page-wrapper">
        <div class="d-flex justify-content-between align-items-center my-4 admin-page-title">
            <div>
                <h1 class="m-0">Ratings</h1>
                <p class="text-muted mb-0">
                    Moderate buyer ratings and comments.
                </p>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if ($errors->has('status') || $errors->has('moderation_note'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ $errors->first('moderation_note') ?: $errors->first('status') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="admin-table-wrapper">
            @include('admin.partials.filter-bar', [
                'action' => route('admin.rating.index'),
                'searchPlaceholder' => 'Search comment, product, seller, or buyer',
                'filters' => [
                    ['name' => 'status', 'label' => 'Status', 'options' => App\Models\Rating::STATUSES],
                    [
                        'name' => 'rating_value',
                        'label' => 'Rating',
                        'options' => [1 => '1 ★', 2 => '2 ★', 3 => '3 ★', 4 => '4 ★', 5 => '5 ★'],
                    ],
                ],
            ])

            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead>
                        <tr>
                            <th scope="col">No</th>
                            <th scope="col">Product</th>
                            <th scope="col">Seller</th>
                            <th scope="col">Buyer</th>
                            <th scope="col">Rating</th>
                            <th scope="col">Comment</th>
                            <th scope="col">Status</th>
                            <th scope="col">Updated At</th>
                            <th scope="col">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($ratings as $rating)
                            @php
                                $item = $rating->transactionItem;
                                $isHidden = $rating->status === 'hidden';
                            @endphp
                            <tr>
                                <td>{{ $ratings->firstItem() + $loop->index }}</td>
                                <td>
                                    <div class="fw-semibold">{{ $item->item_name_snapshot }}</div>
                                    <span class="badge {{ $item->item_type === 'bundle' ? 'bg-dark' : 'bg-secondary' }}">
                                        {{ $item->item_type === 'bundle' ? 'Bundle' : 'Content' }}
                                    </span>
                                </td>
                                <td>{{ $item->seller->name ?? '-' }}</td>
                                <td>
                                    <div class="fw-semibold">{{ $item->transaction->buyer->name ?? '-' }}</div>
                                    <div class="small text-muted">{{ $item->transaction->buyer->email ?? '-' }}</div>
                                </td>
                                <td class="text-nowrap text-warning" aria-label="{{ $rating->rating_value }} out of 5">
                                    {{ str_repeat('★', $rating->rating_value) }}{{ str_repeat('☆', 5 - $rating->rating_value) }}
                                </td>
                                <td class="text-break" style="max-width: 260px;">
                                    {{ $rating->comment ? \Illuminate\Support\Str::limit($rating->comment, 80) : '-' }}
                                </td>
                                <td>
                                    @if ($isHidden)
                                        <span class="badge bg-secondary">Hidden</span>
                                        @if ($rating->moderation_note)
                                            <div class="small text-muted mt-1">{{ $rating->moderation_note }}</div>
                                        @endif
                                    @else
                                        <span class="badge bg-success">Visible</span>
                                    @endif
                                </td>
                                <td class="text-nowrap">{{ $rating->updated_at->format('d M Y H:i') }}</td>
                                <td>
                                    <button type="button"
                                        class="btn btn-sm {{ $isHidden ? 'btn-outline-success' : 'btn-outline-danger' }}"
                                        data-bs-toggle="modal" data-bs-target="#moderateRatingModal-{{ $rating->id }}"
                                        title="{{ $isHidden ? 'Show rating' : 'Hide rating' }}">
                                        <i class="fi {{ $isHidden ? 'fi-rr-eye' : 'fi-rr-eye-crossed' }}"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-4 text-muted">
                                    {{ request()->hasAny(['q', 'status', 'rating_value']) ? 'No ratings match this filter.' : 'No ratings found.' }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                {{ $ratings->links() }}
            </div>
        </div>
    </div>

    @foreach ($ratings as $rating)
        @php
            $isHidden = $rating->status === 'hidden';
        @endphp
        <div class="modal fade" id="moderateRatingModal-{{ $rating->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <form method="POST" action="{{ route('admin.rating.status', $rating) }}" class="modal-content">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="status" value="{{ $isHidden ? 'visible' : 'hidden' }}">

                    <div class="modal-header">
                        <h5 class="modal-title">{{ $isHidden ? 'Show this rating again?' : 'Hide this rating?' }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <div class="text-warning mb-1" aria-hidden="true">
                            {{ str_repeat('★', $rating->rating_value) }}{{ str_repeat('☆', 5 - $rating->rating_value) }}
                        </div>
                        <p class="text-break mb-3" style="white-space: pre-line;">{{ $rating->comment ?: 'No comment.' }}
                        </p>

                        <label for="note-{{ $rating->id }}" class="form-label">
                            {{ $isHidden ? 'Note (optional)' : 'Reason (required)' }}
                        </label>
                        <textarea name="moderation_note" id="note-{{ $rating->id }}" class="form-control" rows="3" maxlength="500"
                            @required(!$isHidden)>{{ $isHidden ? '' : '' }}</textarea>
                        <div class="form-text">
                            {{ $isHidden ? 'The rating will count in the average again.' : 'A hidden rating no longer counts in the average and is not shown publicly.' }}
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn {{ $isHidden ? 'btn-success' : 'btn-danger' }}">
                            {{ $isHidden ? 'Show Rating' : 'Hide Rating' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endforeach
@endsection
