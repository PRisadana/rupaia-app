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
                                <label for="status" :value="__('Status')" class="form-label">Status</label>
                                <select name="status" id="status"
                                    class="form-select @error('status') is-invalid @enderror">
                                    <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>active
                                    </option>
                                    <option value="banned" {{ old('status') == 'banned' ? 'selected' : '' }}>
                                        banned
                                    </option>
                                </select>

                                @error('status')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
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
