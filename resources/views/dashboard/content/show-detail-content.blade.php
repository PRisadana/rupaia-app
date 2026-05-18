@extends('layouts.main')

@section('content')
    <div class="container py-5">
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

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

                        @if ($content->sale_type === 'single_sale')
                            <span class="badge bg-warning text-dark mb-2">
                                Premium
                            </span>
                        @endif

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
                                data-bs-toggle="modal" data-bs-target="#reportContentModal" type="submit"
                                style="width: 42px; height: 42px;" title="Report this content">
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
        @php
            $folderCount = $relatedContents->count();
            $folderMasonryClass = $folderCount < 4 ? 'masonry-gallery-few items-' . $folderCount : 'masonry-gallery';
        @endphp

        <div class="{{ $folderMasonryClass }}">
            @forelse ($relatedContents as $relatedContent)
                <div class="masonry-item">
                    <a href="{{ route('content.detail', $relatedContent->id) }}" class="text-decoration-none">
                        <div class="position-relative content-clean-wrapper shadow-sm overflow-hidden">
                            <img src="{{ asset('storage/' . $relatedContent->path_low_res) }}"
                                alt="{{ $relatedContent->content_title }}" class="img-fluid w-100 content-clean-image"
                                loading="lazy">
                            @if ($relatedContent->sale_type === 'single_sale' && $relatedContent->sale_status === 'available')
                                <span class="badge bg-warning text-dark position-absolute top-0 end-0 m-2"
                                    style="z-index: 10;">
                                    Premium
                                </span>
                            @endif
                        </div>
                    </a>
                </div>
            @empty
                <div class="w-100 text-center py-5" style="column-span: all;">
                    <p class="text-muted fs-4 mt-3">
                        There is no content related from same folder.
                    </p>
                </div>
            @endforelse
        </div>
    </div>

    <h1 class="h3 fw-bold mb-2">More related content</h1>
    <hr>
    <div class="container pb-5">
        @php
            $tagCount = $relatedByTags->count();
            $tagMasonryClass = $tagCount < 4 ? 'masonry-gallery-few items-' . $tagCount : 'masonry-gallery';
        @endphp

        <div class="{{ $tagMasonryClass }}">
            @forelse ($relatedByTags as $relatedByTag)
                <div class="masonry-item">
                    <a href="{{ route('content.detail', $relatedByTag->id) }}" class="text-decoration-none">
                        <div class="position-relative content-clean-wrapper shadow-sm overflow-hidden">
                            <img src="{{ asset('storage/' . $relatedByTag->path_low_res) }}"
                                alt="{{ $relatedByTag->content_title }}" class="img-fluid w-100 content-clean-image"
                                loading="lazy">
                            @if ($relatedByTag->sale_type === 'single_sale' && $relatedByTag->sale_status === 'available')
                                <span class="badge bg-warning text-dark position-absolute top-0 end-0 m-2"
                                    style="z-index: 10;">
                                    Premium
                                </span>
                            @endif
                        </div>
                    </a>
                </div>
            @empty
                <div class="w-100 text-center py-5" style="column-span: all;">
                    <p class="text-muted fs-4 mt-3">
                        There is no content related from same tag.
                    </p>
                </div>
            @endforelse
        </div>

        <div class="modal fade" id="reportContentModal" tabindex="-1" aria-labelledby="reportContentModalLabel"
            aria-hidden="true">
            <div class="modal-dialog">
                <form action="{{ route('content.report', $content->id) }}" method="POST" class="modal-content">
                    @csrf

                    <div class="modal-header">
                        <h5 class="modal-title" id="reportContentModalLabel">Report Content</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        @if ($errors->has('report'))
                            <div class="alert alert-danger">
                                {{ $errors->first('report') }}
                            </div>
                        @endif

                        <div class="mb-3">
                            <label for="reason" class="form-label">Reason</label>
                            <select name="reason" id="reason"
                                class="form-select @error('reason') is-invalid @enderror" required>
                                <option value="">Select Reason</option>
                                <option value="copyright" {{ old('reason') === 'copyright' ? 'selected' : '' }}>
                                    Copyright infringement
                                </option>
                                <option value="inappropriate" {{ old('reason') === 'inappropriate' ? 'selected' : '' }}>
                                    Inappropriate content
                                </option>
                                <option value="misleading" {{ old('reason') === 'misleading' ? 'selected' : '' }}>
                                    Misleading content
                                </option>
                                <option value="spam" {{ old('reason') === 'spam' ? 'selected' : '' }}>
                                    Spam or scam
                                </option>
                                <option value="privacy" {{ old('reason') === 'privacy' ? 'selected' : '' }}>
                                    Privacy violation
                                </option>
                                <option value="other" {{ old('reason') === 'other' ? 'selected' : '' }}>
                                    Other
                                </option>
                            </select>

                            @error('reason')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea name="description" id="description" class="form-control @error('description') is-invalid @enderror"
                                rows="4" placeholder="Describe the issue. Required if you choose Other.">{{ old('description') }}</textarea>

                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror

                            <div class="form-text">
                                Please provide enough information for admin review.
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Submit Report</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

{{-- <style>
    /* --- CSS Masonry Gallery --- */
    .masonry-gallery,
    .masonry-gallery-few {
        column-gap: 1.25rem;
        column-fill: balance;
        width: 100%;
    }

    .masonry-gallery {
        column-count: 2;
    }

    .masonry-gallery-few {
        column-count: 2;
    }

    @media (min-width: 768px) {
        .masonry-gallery {
            column-count: 3;
        }

        .masonry-gallery-few.items-2 {
            column-count: 2;
        }

        .masonry-gallery-few.items-3 {
            column-count: 3;
        }
    }

    @media (min-width: 992px) {
        .masonry-gallery {
            column-count: 4;
        }

        .masonry-gallery-few.items-1 {
            column-count: 1;
            max-width: 400px;
            margin: 0 auto;
        }

        .masonry-gallery-few.items-2 {
            column-count: 2;
            max-width: 800px;
            margin: 0 auto;
        }

        .masonry-gallery-few.items-3 {
            column-count: 3;
            max-width: 1100px;
            margin: 0 auto;
        }
    }

    .masonry-item {
        break-inside: avoid;
        -webkit-column-break-inside: avoid;
        page-break-inside: avoid;
        margin-bottom: 1.25rem;
        display: block;
        width: 100%;
    }

    /* --- Efek Hover & Wrapper --- */
    .content-clean-wrapper {
        overflow: hidden;
        border-radius: 0.75rem;
        background: #f8f9fa;
        display: block;
    }

    .content-clean-image {
        display: block;
        width: 100%;
        height: auto;
        transition: transform 0.35s ease;
    }

    .content-clean-wrapper:hover .content-clean-image {
        transform: scale(1.05);
    }
</style> --}}
