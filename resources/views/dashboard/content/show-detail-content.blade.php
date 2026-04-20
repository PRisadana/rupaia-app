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

                        <a href="{{ route('folder.show', $content->folder->id) }}" class=" text-decoration-none">
                            <p class="text-muted mb-2">
                                Folder <strong>{{ $content->folder->folder_name ?? '-' }}
                                </strong> </p>
                        </a>

                        <a href="{{ route('authors.show', $content->user->id) }}"
                            class="d-inline-flex align-items-center text-decoration-none">
                            <img src="{{ $content->user->profile_photo_path ? asset('storage/' . $content->user->profile_photo_path) : asset('aset/rupaia_logo.png') }}"
                                alt="Profile" class="rounded-circle flex-shrink-0" width="30" height="30"
                                style="object-fit: cover;">
                            <p class="text-muted m-2">
                                by <strong>{{ $content->user->name ?? 'Unknown Author' }}</strong>
                            </p>
                        </a>
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
                                    <span class="badge text-bg-light border">
                                        {{ $tag->tag_name }}
                                    </span>
                                @endforeach
                            </div>
                        @else
                            <p class="text-muted mb-0">No tags available.</p>
                        @endif
                    </div>

                    <div class="mb-4">
                        <h3 class="fw-bold text-dark mb-0">
                            Rp {{ number_format($content->price ?? 0, 0, ',', '.') }}
                        </h3>
                    </div>

                    <div class="d-flex flex-column flex-md-row gap-2">
                        <button class="btn btn-dark flex-grow-1 d-flex align-items-center justify-content-center gap-2"
                            type="submit">
                            <i class="fi fi-rr-shopping-cart-add"></i>
                            <span>Add to Cart</span>
                        </button>

                        <a href="{{ route('editing.preview', $content->id) }}"
                            class="btn btn-outline-dark flex-grow-1 d-flex align-items-center justify-content-center gap-2 text-decoration-none">
                            <i class="fi fi-rr-pencil"></i>
                            <span>Try Editing</span>
                        </a>

                        <div class="d-flex justify-content-end">
                            <button
                                class="btn btn-outline-danger d-flex align-items-center justify-content-center flex-shrink-0"
                                type="submit" style="width: 42px; height: 42px;" title="Report this content">
                                <i class="fi fi-rr-flag-alt m-0"></i>
                            </button>
                        </div>
                    </div>

                    {{-- @auth
                            @if (auth()->id() === $content->seller_id)
                                <a href="{{ route('content.edit', $content->id) }}" class="btn btn-outline-secondary">
                                    Edit Content
                                </a>
                            @endif
                        @endauth --}}

                    <a href="{{ route('dashboard') }}" class="btn btn-link-light text-decoration-none mt-2">
                        ← Back to Explore
                    </a>
                </div>
            </div>
        </div>
    </div>

    <h1 class="h3 fw-bold mb-2">More from the same folder</h1>
    <hr>
    <div class="container pb-5">
        <div class="row g-3">
            @forelse ($relatedContents as $relatedContent)
                <div class="col-6 col-md-4 col-lg-3">
                    <a href="{{ route('content.detail', $relatedContent->id) }}" class="text-decoration-none">
                        <div class="content-clean-wrapper">
                            <img src="{{ asset('storage/' . $relatedContent->path_low_res) }}"
                                alt="{{ $relatedContent->content_title }}"
                                class="img-fluid w-100 rounded-3 shadow-sm content-clean-image">
                        </div>
                    </a>
                </div>
            @empty
                <div class="col-12">
                    <p class="text-center text-muted fs-4 mt-5">
                        There is no content related from same folder.
                    </p>
                </div>
            @endforelse
        </div>
    </div>

    <h1 class="h3 fw-bold mb-2">More related content</h1>
    <hr>
    <div class="container pb-5">
        <div class="row g-3">
            @forelse ($relatedByTags as $relatedByTag)
                <div class="col-6 col-md-4 col-lg-3">
                    <a href="{{ route('content.detail', $relatedByTag->id) }}" class="text-decoration-none">
                        <div class="content-clean-wrapper">
                            <img src="{{ asset('storage/' . $relatedByTag->path_low_res) }}"
                                alt="{{ $relatedByTag->content_title }}"
                                class="img-fluid w-100 rounded-3 shadow-sm content-clean-image">
                        </div>
                    </a>
                </div>
            @empty
                <div class="col-12">
                    <p class="text-center text-muted fs-4 mt-5">
                        There is no content related from same tag.
                    </p>
                </div>
            @endforelse
        </div>
    </div>
@endsection
