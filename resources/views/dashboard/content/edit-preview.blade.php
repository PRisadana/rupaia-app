@extends('layouts.main')

@section('content')
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
    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 fw-bold mb-1">Editing Preview</h1>
                <p class="text-muted mb-0">
                    Applying presets to: <strong>{{ $content->content_title }}</strong>
                </p>
            </div>
            <a href="{{ route('content.detail', $content->id) }}" class="btn btn-outline-secondary px-4">
                ← Back to Detail
            </a>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger rounded-4">
                <strong>Some errors occurred:</strong>
                <ul class="mb-0 mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="row g-4 mb-4">
            <div class="col-lg-6">
                <div class="bg-white rounded-4 shadow-sm p-3 h-100 d-flex flex-column">
                    <h5 class="fw-semibold text-muted text-center mb-3">Before</h5>
                    <div
                        class="flex-grow-1 d-flex align-items-center justify-content-center bg-light rounded-3 overflow-hidden">
                        <img src="{{ asset('storage/' . $content->path_low_res) }}" alt="Original Image"
                            class="img-fluid w-100 shadow-sm" style="object-fit: contain; max-height: 450px;">
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div
                    class="bg-white rounded-4 shadow-sm p-3 h-100 d-flex flex-column border-2 border-primary border-opacity-50">
                    <h5 class="fw-semibold text-primary text-center mb-3">After</h5>
                    <div class="flex-grow-1 d-flex align-items-center justify-content-center bg-light rounded-3 overflow-hidden"
                        style="min-height: 350px;">
                        @if (!empty($selectedPresetId))
                            <img src="{{ route('content.image-preview', ['content' => $content->id, 'preset_id' => $selectedPresetId]) }}"
                                alt="Edited Preview" class="img-fluid w-100 shadow-sm"
                                style="object-fit: contain; max-height: 450px;">
                        @else
                            <div
                                class="text-center text-muted d-flex flex-column justify-content-center align-items-center h-100 p-5">
                                <i class="fi fi-rr-picture mb-2" style="font-size: 3rem; opacity: 0.5;"></i>
                                <p class="mb-0 fs-5">No preview yet</p>
                                <small>Select a preset below to see the magic.</small>
                            </div>
                        @endif

                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="bg-white rounded-4 shadow-sm p-4">
                    <div class="row align-items-end g-3">
                        <div class="col-md-6 col-lg-5">
                            <form action="{{ route('editing.preview', $content->id) }}" method="GET"
                                class="row align-items-end g-3">
                                <label for="presetSelect" class="form-label fw-semibold text-dark">Choose a LUT
                                    Preset</label>
                                <select class="form-select form-select-sm @error('preset_id') is-invalid @enderror"
                                    id="presetSelect" name="preset_id" required onchange="this.form.submit()">
                                    <option value="" disabled {{ empty($selectedPresetId) ? 'selected' : '' }}>
                                        Select a Color Preset
                                    </option>

                                    @foreach ($presets as $preset)
                                        <option value="{{ $preset->id }}"
                                            {{ (string) $selectedPresetId === (string) $preset->id ? 'selected' : '' }}>
                                            {{ $preset->preset_name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('preset_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                        </div>
                        </form>
                        <div class="col-md-6 col-lg-5">
                            @if ($selectedPresetId)
                                <form method="POST" action="{{ route('cart.content.store', $content) }}">
                                    @csrf
                                    <input type="hidden" name="preset_id" value="{{ $selectedPresetId }}">
                                    <button type="submit"
                                        class="btn btn-success btn-sm px-4d-flex align-items-center gap-2">
                                        <i class="fi fi-rr-shopping-cart-add"></i>
                                        Add to Cart
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
