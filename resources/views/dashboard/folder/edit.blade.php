@extends('layouts.main')

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-10 ">
            <div class="card shadow-sm mb-4">
                <div class="card-body p-4 p-md-5">
                    <header>
                        <h2 class="card-title mb-3 text-center">
                            {{ __('Edit Your Folder!') }}
                        </h2>
                    </header>

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

                    <form method="POST" action="{{ route('folder.update', $folder) }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <script>
                            document.addEventListener('DOMContentLoaded', function() {
                                const isBundleSelect = document.getElementById('is_bundle');
                                const bundleFields = document.getElementById('bundleFields');
                                const collectionInfo = document.getElementById('collectionInfo');

                                function toggleFolderTypeFields() {
                                    if (isBundleSelect.value === '1') {
                                        bundleFields.classList.remove('d-none');
                                        collectionInfo.classList.add('d-none');
                                    } else {
                                        bundleFields.classList.add('d-none');
                                        collectionInfo.classList.remove('d-none');
                                    }
                                }

                                isBundleSelect.addEventListener('change', toggleFolderTypeFields);
                                toggleFolderTypeFields();
                            });
                        </script>

                        <div class="mb-3">
                            <label for="folder_name" :value="__('Folder Name')" class="form-label">Folder Name</label>
                            <input id="folder_name" name="folder_name" type="text"
                                value="{{ old('folder_name', $folder->folder_name) }}"
                                class="form-control @error('folder_name') is-invalid @enderror">

                            @error('folder_name')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="folder_description" :value="__('Folder Description')" class="form-label">Folder
                                Description</label>
                            <input id="folder_description" name="folder_description" type="text"
                                value="{{ old('folder_description', $folder->folder_description) }}"
                                class="form-control
                                @error('folder_description') is-invalid @enderror">

                            @error('folder_description')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="visibility" :value="__('Folder Visibility')" class="form-label">Folder
                                Visibility</label>
                            <select name="visibility" id="visibility"
                                class="form-select @error('visibility') is-invalid @enderror">
                                <option value="public"
                                    {{ old('visibility', $folder->visibility) == 'public' ? 'selected' : '' }}>
                                    Public
                                </option>
                                <option value="private"
                                    {{ old('visibility', $folder->visibility) == 'private' ? 'selected' : '' }}>
                                    Private
                                </option>
                                {{-- <option value="by_request"
                                    {{ old('visibility', $folder->visibility) == 'by_request' ? 'selected' : '' }}>
                                    By Request
                                </option> --}}
                            </select>

                            @error('visibility')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="is_bundle" class="form-label">Folder Type</label>
                            <select name="is_bundle" id="is_bundle"
                                class="form-select @error('is_bundle') is-invalid @enderror" required>
                                <option value="0"
                                    {{ old('is_bundle', (int) $folder->is_bundle) == 0 ? 'selected' : '' }}>
                                    Collection Folder
                                </option>
                                <option value="1"
                                    {{ old('is_bundle', (int) $folder->is_bundle) == 1 ? 'selected' : '' }}>
                                    Bundle Folder
                                </option>
                            </select>

                            @error('is_bundle')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div id="collectionInfo"
                            class="alert alert-secondary {{ old('is_bundle', (int) $folder->is_bundle) == 0 ? '' : 'd-none' }}">
                            <strong>Collection Folder</strong>
                            <div class="small mt-1">
                                This folder is used only to organize contents. Bundle price and folder license are not
                                applied.
                                Each content inside this folder uses its own license.
                            </div>
                        </div>

                        <div id="bundleFields"
                            class="{{ old('is_bundle', (int) $folder->is_bundle) == 1 ? '' : 'd-none' }}">
                            <div class="alert alert-info">
                                Bundle price, license, and individual sale setting are only used when this folder is sold as
                                a bundle.
                            </div>

                            @if ($folder->is_bundle)
                                <div class="alert alert-warning">
                                    <strong>Important:</strong>
                                    Changing the bundle license will also update the license of all direct contents inside
                                    this folder.
                                    Changing individual sale setting will affect whether the contents can be purchased
                                    individually.
                                </div>
                            @else
                                <div class="alert alert-warning">
                                    <strong>Important:</strong>
                                    If this collection is changed into a bundle, the selected bundle license will be applied
                                    to all direct contents inside this folder.
                                </div>
                            @endif

                            <div class="mb-3">
                                <label for="bundle_price" class="form-label">Bundle Price</label>
                                <input id="bundle_price" name="bundle_price" type="number" min="0" step="0.01"
                                    class="form-control @error('bundle_price') is-invalid @enderror"
                                    value="{{ old('bundle_price', $folder->bundle_price) }}">

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
                                            {{ old('license_id', $folder->license_id) == $license->id ? 'selected' : '' }}>
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
                                    <option value="1"
                                        {{ old('allow_individual_sale', (int) $folder->allow_individual_sale) == 1 ? 'selected' : '' }}>
                                        Yes
                                    </option>
                                    <option value="0"
                                        {{ old('allow_individual_sale', (int) $folder->allow_individual_sale) == 0 ? 'selected' : '' }}>
                                        No
                                    </option>
                                </select>

                                @error('allow_individual_sale')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <button class="w-100 btn btn-lg btn-dark" type="submit">
                            Update Folder
                        </button>
                    </form>
                </div>
            </div>
        </div>
    @endsection
