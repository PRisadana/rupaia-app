@extends('layouts.admin')

@section('content')
    <div class="container-fluid px-4 admin-page-wrapper">
        <div class="row justify-content-center">
            <div class="col-12 col-lg-8 col-xl-7">
                <div class="card admin-card">
                    <div class="card-body p-4 p-md-5">
                        <div class="mb-4 text-center">
                            <h2 class="card-title mb-1">
                                {{ __('Edit Content') }}
                            </h2>
                            <p class="text-muted mb-0">
                                Update content information.
                            </p>
                        </div>

                        <form method="POST" action="{{ route('admin.content.status.update', $content) }}"
                            enctype="multipart/form-data">
                            @csrf
                            @method('PUT')

                            <div class="mb-4">
                                <label class="form-label">
                                    <h4>Content Preview</h4>
                                </label>

                                @if ($content->path_low_res)
                                    <div class="border rounded-3 p-2 bg-light">
                                        <img src="{{ asset('storage/' . $content->path_low_res) }}"
                                            alt="{{ $content->content_title }}" class="img-fluid rounded"
                                            style="max-height: 320px; object-fit: contain;">
                                    </div>
                                @else
                                    <p class="text-muted mb-0">No preview available.</p>
                                @endif
                            </div>

                            <div class="mb-4">
                                <label class="form-label">
                                    <h4>Validation Result</h4>
                                </label>

                                <div class="border rounded-3 p-3 bg-light">
                                    <div class="mb-2">
                                        <strong>Validation Reason:</strong>
                                        <div class="text-muted">
                                            {{ $content->validation_reason ?? '-' }}
                                        </div>
                                    </div>

                                    <div class="mb-2">
                                        <strong>Similarity Distance:</strong>
                                        <div class="text-muted">
                                            {{ $content->similarity_distance ?? '-' }}
                                        </div>
                                    </div>

                                    <div class="mb-2">
                                        <strong>Validated At:</strong>
                                        <div class="text-muted">
                                            {{ $content->validated_at?->format('d M Y H:i') ?? '-' }}
                                        </div>
                                    </div>

                                    <div class="mb-2">
                                        <strong>Moderation Category:</strong>
                                        <div class="text-muted">
                                            {{ $content->moderation_category ?? '-' }}
                                        </div>
                                    </div>

                                    <div class="mb-2">
                                        <strong>Moderation Score:</strong>
                                        <div class="text-muted">
                                            {{ $content->moderation_score ?? '-' }}
                                        </div>
                                    </div>

                                    <div>
                                        <strong>Reviewed By:</strong>
                                        <div class="text-muted">
                                            {{ $content->reviewer?->name ?? '-' }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label">
                                    <h4>Similar Content</h4>
                                </label>

                                @if ($content->similarContent)
                                    <div class="row g-3">
                                        <div class="col-md-5">
                                            <div class="border rounded-3 p-2 bg-light h-100">
                                                <img src="{{ asset('storage/' . $content->similarContent->path_low_res) }}"
                                                    alt="{{ $content->similarContent->content_title }}"
                                                    class="img-fluid rounded mb-2"
                                                    style="width: 100%; max-height: 240px; object-fit: contain;">

                                                <div class="fw-semibold">
                                                    {{ $content->similarContent->content_title }}
                                                </div>

                                                <div class="small text-muted">
                                                    Author: {{ $content->similarContent->user?->name ?? '-' }}
                                                </div>

                                                <div class="small text-muted">
                                                    Folder: {{ $content->similarContent->folder?->folder_name ?? '-' }}
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-7">
                                            <div class="alert alert-warning mb-0">
                                                This content was detected as visually similar to an existing content.
                                                Please compare the preview before approving or rejecting it.
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <p class="text-muted mb-0">
                                        No similar content detected.
                                    </p>
                                @endif
                            </div>

                            <div class="mb-3">
                                <label class="form-label">
                                    <h4>Author</h4>
                                </label>
                                <div class="form-control-plaintext">
                                    {{ $content->user->name }}
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">
                                    <h4>Content Title</h4>
                                </label>
                                <div class="form-control-plaintext">
                                    {{ $content->content_title }}
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="status" class="form-label">Status</label>
                                <select name="status" id="status"
                                    class="form-select @error('status') is-invalid @enderror" required>
                                    <option value="pending_review"
                                        {{ old('status', $content->status) === 'pending_review' ? 'selected' : '' }}>
                                        Pending Review
                                    </option>
                                    <option value="active"
                                        {{ old('status', $content->status) === 'active' ? 'selected' : '' }}>
                                        Active
                                    </option>
                                    <option value="rejected"
                                        {{ old('status', $content->status) === 'rejected' ? 'selected' : '' }}>
                                        Rejected
                                    </option>
                                    <option value="banned"
                                        {{ old('status', $content->status) === 'banned' ? 'selected' : '' }}>
                                        Banned
                                    </option>
                                </select>

                                @error('status')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="review_note" class="form-label">Review Note</label>
                                <textarea name="review_note" id="review_note" rows="4"
                                    class="form-control @error('review_note') is-invalid @enderror" placeholder="Write admin review note if needed.">{{ old('review_note', $content->review_note) }}</textarea>

                                @error('review_note')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror

                                <div class="form-text">
                                    This note is used to record the admin decision for this content.
                                </div>
                            </div>

                            <button class="w-100 btn btn-lg btn-dark" type="submit">
                                Update Content Status
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
