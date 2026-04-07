@extends('layouts.main')

@section('content')
    <div class="container py-5">
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
                            type="submit" style="width: 42px; height: 42px;" title="Report this content">
                            <i class="fi fi-rr-flag-alt m-0"></i>
                        </button>
                    </div>
                </div>

                <a href="{{ route('dashboard') }}" class="btn btn-link-light text-decoration-none mt-2">
                    ← Back to Explore
                </a>
            </div>
        </div>
    </div>
    </div>
    </div>
@endsection
