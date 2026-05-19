@extends('layouts.admin')

@section('content')
    <div class="container-fluid px-4 admin-page-wrapper">
        <div class="row justify-content-center">
            <div class="col-12 col-lg-8 col-xl-7">
                <div class="card admin-card">
                    <div class="card-body p-4 p-md-5">
                        <div class="mb-4 text-center">
                            <h2 class="card-title mb-1">
                                {{ __('Edit Showcase Item') }}
                            </h2>
                            <p class="text-muted mb-0">
                                Update showcase item moderation status.
                            </p>
                        </div>

                        <form method="POST" action="{{ route('admin.showcase.status.update', $showcaseItem) }}">
                            @csrf
                            @method('PUT')

                            <div class="mb-4">
                                <label class="form-label fw-semibold">Author</label>
                                <div class="form-control-plaintext border rounded-3 px-3 py-2 bg-light">
                                    {{ $showcaseItem->user->name }}
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-semibold">Description</label>
                                <div class="form-control-plaintext border rounded-3 px-3 py-2 bg-light">
                                    {{ $showcaseItem->description ?: 'No description available.' }}
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="status" class="form-label fw-semibold">Status</label>
                                <select name="status" id="status"
                                    class="form-select @error('status') is-invalid @enderror">
                                    <option value="active"
                                        {{ old('status', $showcaseItem->status) === 'active' ? 'selected' : '' }}>
                                        Active
                                    </option>
                                    <option value="banned"
                                        {{ old('status', $showcaseItem->status) === 'banned' ? 'selected' : '' }}>
                                        Banned
                                    </option>
                                </select>

                                @error('status')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <div class="d-flex flex-column flex-md-row gap-2">
                                <button class="btn btn-dark w-100" type="submit">
                                    Update Showcase Item
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
