@extends('layouts.main')

@section('content')
    <div class="container py-5">
        <div class="row g-4">
            <div class="col-lg-7">
                <div class="bg-white rounded-4 shadow-sm p-3">
                    <img src="{{ asset('storage/' . $content->path_low_res) }}" alt="{{ $content->content_title }}"
                        class="img-fluid w-100 rounded-4">
                </div>
            </div>

            <div class="col-lg-5">
                <div class="bg-white rounded-4 shadow-sm p-4 h-100">
                    <div class="mb-3">
                        <h1 class="h3 fw-bold mb-2">{{ $content->content_title }}</h1>

                        <p class="text-muted mb-1">
                            by <strong>{{ $content->user->name ?? 'Unknown Author' }}</strong>
                        </p>

                        <p class="text-muted mb-0">
                            Folder: {{ $content->folder->folder_name ?? '-' }}
                        </p>
                    </div>

                    <hr>

                    <div class="mb-4">
                        <h6 class="fw-semibold">Description</h6>
                        <p class="text-muted mb-0">
                            {{ $content->content_description ?: 'No description available.' }}
                        </p>
                    </div>

                    <div class="mb-4">
                        <h6 class="fw-semibold">Tags</h6>
                        @if ($content->tags->count())
                            <div class="d-flex flex-wrap gap-2">
                                @foreach ($content->tags as $tag)
                                    <span class="badge rounded-pill text-bg-light border">
                                        {{ $tag->tag_name }}
                                    </span>
                                @endforeach
                            </div>
                        @else
                            <p class="text-muted mb-0">No tags available.</p>
                        @endif
                    </div>

                    <div class="mb-4">
                        <h6 class="fw-semibold">Price</h6>
                        <h3 class="fw-bold text-primary mb-0">
                            Rp {{ number_format($content->price ?? 0, 0, ',', '.') }}
                        </h3>
                    </div>

                    <div class="d-grid gap-2">
                        <button class="btn btn-primary btn-lg">
                            Add to Cart
                        </button>

                        <button class="btn btn-outline-danger">
                            Report Content
                        </button>

                        {{-- @auth
                            @if (auth()->id() === $content->seller_id)
                                <a href="{{ route('content.edit', $content->id) }}" class="btn btn-outline-secondary">
                                    Edit Content
                                </a>
                            @endif
                        @endauth --}}

                        <a href="{{ route('dashboard') }}" class="btn btn-link text-decoration-none">
                            ← Back to Explore
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
