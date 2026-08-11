@extends('layouts.admin')

@section('content')
    <div class="container-fluid px-4 admin-page-wrapper">
        <div class="row justify-content-center">
            <div class="col-12 col-lg-8 col-xl-7">
                <div class="card admin-card">
                    <div class="card-body p-4 p-md-5">
                        <div class="mb-4 text-center">
                            <h2 class="card-title mb-1">
                                {{ __('Edit License') }}
                            </h2>
                            <p class="text-muted mb-0">
                                Update license information.
                            </p>
                        </div>

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

                        @if (session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('admin.license.update', $license) }}"
                            enctype="multipart/form-data">
                            @csrf
                            @method('PUT')

                            <div class="mb-3">
                                <label for="name" :value="__('License Name')" class="form-label">License Name</label>
                                <input id="name" name="name" type="text"
                                    value="{{ old('name', $license->name) }}"
                                    class="form-control @error('name') is-invalid @enderror">

                                @error('name')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="code" :value="__('License code')" class="form-label">License
                                    code</label>
                                <input id="code" name="code" type="text"
                                    value="{{ old('code', $license->code) }}"
                                    class="form-control @error('code') is-invalid @enderror">

                                @error('code')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="description" :value="__('Description')" class="form-label">Description</label>
                                <textarea id="description" name="description" rows="3"
                                    class="form-control @error('description') is-invalid @enderror">{{ old('description', $license->description) }}</textarea>

                                @error('description')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="terms" :value="__('Terms')" class="form-label">Terms</label>
                                <textarea id="terms" name="terms" rows="3" class="form-control @error('terms') is-invalid @enderror">{{ old('terms', $license->terms) }}</textarea>

                                @error('terms')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="is_active" :value="__('Is Active')" class="form-label">Is Active</label>
                                <select id="is_active" name="is_active"
                                    class="form-control @error('is_active') is-invalid @enderror">
                                    <option value="1">Yes</option>
                                    <option value="0">No</option>
                                </select>

                                @error('is_active')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <button class="w-100 btn btn-lg btn-dark" type="submit">
                                Update License
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
