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

                    @php
                        $imagePath = $showcaseItem->custom_path ?? $showcaseItem->content?->path_low_res;
                    @endphp

                    <img src="{{ $imagePath ? asset('storage/' . $imagePath) : asset('aset/rupaia_logo.png') }}"
                        class="img-fluid w-100 rounded-4">
                </div>
            </div>

            <div class="col-lg-5">
                <div class="bg-white rounded-4 shadow-sm p-4 h-100">


                    <div class="mb-4">
                        <h6 class="fw-semibold">Description</h6>
                        <p class="text-muted mb-0">
                            {{ $showcaseItem->description ?: 'No description available.' }}
                        </p>
                    </div>

                    <div class="d-flex justify-content-start">
                        <button
                            class="btn btn-outline-danger d-flex align-items-center justify-content-center flex-shrink-0"
                            data-bs-toggle="modal" data-bs-target="#reportshowcaseModal" type="submit"
                            style="width: 42px; height: 42px;" title="Report this content">
                            <i class="fi fi-rr-flag-alt m-0"></i>
                        </button>
                    </div>
                </div>

                <a href="{{ route('dashboard') }}" class="btn btn-link-light text-decoration-none mt-2">
                    ← Back to Explore
                </a>
            </div>
        </div>

        <div class="modal fade" id="reportshowcaseModal" tabindex="-1" aria-labelledby="reportshowcaseModalLabel"
            aria-hidden="true">
            <div class="modal-dialog">
                <form action="{{ route('showcase.report', $showcaseItem->id) }}" method="POST" class="modal-content">
                    @csrf

                    <div class="modal-header">
                        <h5 class="modal-title" id="reportshowcaseModalLabel">Report Showcase</h5>
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
                            <select name="reason" id="reason" class="form-select @error('reason') is-invalid @enderror"
                                required>
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
