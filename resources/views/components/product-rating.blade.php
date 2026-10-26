<div class="bg-white rounded-4 shadow-sm p-4 my-4">
    <h5 class="fw-bold mb-3">Ratings</h5>

    @if ($summary['count'] === 0)
        <p class="text-muted mb-0">No ratings yet.</p>
    @else
        @php $rounded = (int) round($summary['average']); @endphp

        <div class="d-flex align-items-center gap-2">
            <span class="fs-3 fw-bold">{{ number_format($summary['average'], 1) }}</span>
            <span class="text-warning fs-5" aria-hidden="true">
                {{ str_repeat('★', $rounded) }}{{ str_repeat('☆', 5 - $rounded) }}
            </span>
            <span class="text-muted">
                ({{ $summary['count'] }} {{ \Illuminate\Support\Str::plural('rating', $summary['count']) }})
            </span>
        </div>

        @foreach ($summary['reviews'] as $review)
            <div class="border-top pt-3 mt-3">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <strong>{{ $review->reviewerName() }}</strong>
                        <span class="text-warning ms-1" aria-hidden="true">
                            {{ str_repeat('★', $review->rating_value) }}{{ str_repeat('☆', 5 - $review->rating_value) }}
                        </span>
                    </div>
                    <small class="text-muted">{{ $review->updated_at->format('d M Y') }}</small>
                </div>
                <p class="mb-0 mt-1 text-break" style="white-space: pre-line;">{{ $review->comment }}</p>
            </div>
        @endforeach
    @endif
</div>
