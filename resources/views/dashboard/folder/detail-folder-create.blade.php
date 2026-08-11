@extends('layouts.main')

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-10 ">
            <div class="card shadow-sm mb-4">
                <div class="card-body p-4 p-md-5">
                    <header>
                        <h2 class="card-title mb-3 text-center">
                            {{ __('Add Your Subfolder!') }}
                        </h2>
                    </header>

                    {{-- @if ($errors->any())
                        <div class="alert alert-danger">
                            <strong>Terjadi kesalahan:</strong>
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif --}}

                    <form method="POST" action="{{ route('detail.folder.store') }}" enctype="multipart/form-data">
                        @csrf

                        <input type="hidden" name="parent_id" value="{{ $parentFolder?->id }}">
                        <input type="hidden" name="visibility" value="{{ $parentFolder->visibility }}">
                        {{-- <input type="hidden" name="is_bundle" value="{{ $parentFolder->is_bundle }}"> --}}

                        <script>
                            document.addEventListener('DOMContentLoaded', function() {
                                const isBundleSelect = document.getElementById('is_bundle');
                                const bundleFields = document.getElementById('bundleFields');

                                function toggleBundleFields() {
                                    if (isBundleSelect.value === '1') {
                                        bundleFields.classList.remove('d-none');
                                    } else {
                                        bundleFields.classList.add('d-none');
                                    }
                                }

                                isBundleSelect.addEventListener('change', toggleBundleFields);
                                toggleBundleFields();
                            });
                        </script>

                        <div class="mb-3">
                            <h3 class="form-label">{{ $parentFolder->folder_name }}</h3>
                            <div class="form-text">
                                The folder above is the previous root folder.
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="folder_name" :value="__('Folder Name')" class="form-label">Subfolder Name</label>
                            <input id="folder_name" name="folder_name" type="text"
                                class="form-control @error('folder_name') is-invalid @enderror">

                            @error('folder_name')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="folder_description" :value="__('Subfolder Description')"
                                class="form-label">Subfolder
                                Description</label>
                            <input id="folder_description" name="folder_description" type="text"
                                class="form-control @error('folder_description') is-invalid @enderror">

                            @error('folder_description')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="is_bundle" class="form-label">Folder Type</label>
                            <select name="is_bundle" id="is_bundle"
                                class="form-select @error('is_bundle') is-invalid @enderror" required>
                                <option value="0" {{ old('is_bundle') == 0 ? 'selected' : '' }}>
                                    Collection Folder
                                </option>
                                <option value="1" {{ old('is_bundle') == 1 ? 'selected' : '' }}>
                                    Bundle Folder
                                </option>
                            </select>

                            @error('is_bundle')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div id="bundleFields" class="{{ old('is_bundle') == 1 ? '' : 'd-none' }}">
                            <div class="alert alert-info">
                                Bundle price, license, and individual sale setting are only used when this folder is sold as
                                a bundle.
                            </div>

                            <div class="mb-3">
                                <label for="bundle_price" class="form-label">Bundle Price</label>
                                <input id="bundle_price" name="bundle_price" type="number" min="0" step="0.01"
                                    class="form-control @error('bundle_price') is-invalid @enderror"
                                    value="{{ old('bundle_price') }}">

                                @error('bundle_price')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="license_id" class="form-label">Bundle License</label>
                                <select id="license_id" name="license_id"
                                    class="form-select @error('license_id') is-invalid @enderror">
                                    <option value="">Choose bundle license</option>

                                    @foreach ($licenses as $license)
                                        <option value="{{ $license->id }}"
                                            {{ old('license_id') == $license->id ? 'selected' : '' }}>
                                            {{ $license->name }}
                                        </option>
                                    @endforeach
                                </select>

                                @error('license_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="allow_individual_sale" class="form-label">Allow Individual Sale</label>
                                <select id="allow_individual_sale" name="allow_individual_sale"
                                    class="form-select @error('allow_individual_sale') is-invalid @enderror">
                                    <option value="1" {{ old('allow_individual_sale', 0) == 1 ? 'selected' : '' }}>
                                        Yes
                                    </option>
                                    <option value="0" {{ old('allow_individual_sale', 0) == 0 ? 'selected' : '' }}>
                                        No
                                    </option>
                                </select>

                                @error('allow_individual_sale')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <h3 class="form-label">{{ $parentFolder->visibility }}</h3>
                            <div class="form-text">
                                Visibility above follows the folder in use.
                            </div>
                        </div>

                        <button class="w-100 btn btn-lg btn-dark" type="submit">
                            Add Subfolder
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
