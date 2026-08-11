@extends('layouts.main')

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-10 ">
            <div class="card shadow-sm mb-4">
                <div class="card-body p-4 p-md-5">
                    <header>
                        <h2 class="card-title mb-3 text-center">
                            {{ __('Upload Your Content!') }}
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

                    <form method="POST" action="{{ route('content.detail.folder.store') }}" enctype="multipart/form-data">
                        @csrf

                        @php
                            $isBundleOnly = $parentFolder->is_bundle && !$parentFolder->allow_individual_sale;
                        @endphp

                        <input type="hidden" name="folder_id" value="{{ $parentFolder->id }}">
                        {{-- <input type="hidden" name="visibility" value="{{ $parentFolder->visibility }}"> --}}
                        @if ($parentFolder->is_bundle)
                            <input type="hidden" name="license_id" value="{{ $parentFolder->license_id }}">
                        @endif
                        {{-- <input type="hidden" name="is_bundle" value="{{ $parentFolder->is_bundle }}"> --}}

                        <div class="mb-3">
                            <label for="content_title" :value="__('Content Title')" class="form-label">Content Title</label>
                            <input id="content_title" name="content_title" type="text" value="{{ old('content_title') }}"
                                class="form-control @error('content_title') is-invalid @enderror">

                            @error('content_title')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="content_description" :value="__('Content Description')" class="form-label">Content
                                Description</label>
                            <input id="content_description" name="content_description" type="text"
                                value="{{ old('content_description') }}"
                                class="form-control @error('content_description') is-invalid @enderror">

                            @error('content_description')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="path_hi_res" class="form-label">{{ __('Content') }}</label>
                            <input id="path_hi_res" name="path_hi_res" type="file"
                                class="form-control @error('path_hi_res') is-invalid @enderror"
                                accept="image/jpeg,image/png,image/jpg">

                            @error('path_hi_res')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror

                            <div class="form-text">
                                Supported formats: JPG, JPEG, PNG. The uploaded content will follow the selected folder
                                rules.
                            </div>
                        </div>

                        <div id="contentPreviewWrapper" class="mb-3 d-none">
                            {{-- <label class="form-label">Preview</label> --}}
                            <div class="border rounded-3 p-2 bg-light d-inline-block">
                                <img id="contentPreviewImage" src="" alt="Content preview" class="rounded"
                                    style="width: 160px; object-fit: cover;">
                                <div id="contentPreviewName" class="small text-muted mt-2 text-truncate"
                                    style="max-width: 160px;"></div>
                                <div id="contentPreviewSize" class="small text-muted"></div>
                            </div>
                        </div>

                        <div class="alert {{ $parentFolder->is_bundle ? 'alert-warning' : 'alert-light border' }}">
                            <strong>
                                {{ $parentFolder->is_bundle ? 'Bundle Folder Rules' : 'Collection Folder Rules' }}
                            </strong>

                            <div class="small mt-1">
                                @if ($parentFolder->is_bundle)
                                    This folder is a bundle. The uploaded content will automatically use the bundle license
                                    and sale type will be set to Multi Sale.

                                    @if ($parentFolder->allow_individual_sale)
                                        This bundle allows individual sale, so content price can still be used.
                                    @else
                                        This bundle does not allow individual sale, so content price is not used.
                                        The content will only be available through the bundle.
                                    @endif
                                @else
                                    This folder is a collection. The uploaded content uses its own license, price,
                                    and sale type.
                                @endif
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Selected Folder</label>
                                <input type="text" class="form-control" value="{{ $parentFolder->folder_name }}"
                                    disabled>

                                <div class="form-text">
                                    The content will be uploaded into this folder.
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Folder Type</label>
                                <input type="text" class="form-control"
                                    value="{{ $parentFolder->is_bundle ? 'Bundle' : 'Collection' }}" disabled>

                                <div class="form-text">
                                    {{ $parentFolder->is_bundle
                                        ? 'This folder can be sold as a bundle.'
                                        : 'This folder is used to organize contents.' }}
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Content Visibility</label>
                            <input type="text" class="form-control"
                                value="{{ ucfirst(str_replace('_', ' ', $parentFolder->visibility)) }}" disabled>

                            <div class="form-text">
                                The visibility of the content will follow the visibility of the folder.
                            </div>
                        </div>

                        @if ($parentFolder->is_bundle)
                            <div class="mb-3">
                                <label class="form-label">Bundle License</label>
                                <input type="text" class="form-control"
                                    value="{{ $parentFolder->license->name ?? 'Bundle license not set' }}" disabled>

                                <div class="form-text">
                                    This content will automatically use the bundle folder license.
                                </div>
                            </div>
                        @else
                            <div class="mb-3">
                                <label for="license_id" class="form-label">Content License</label>
                                <select class="form-select @error('license_id') is-invalid @enderror" id="license_id"
                                    name="license_id" required>
                                    <option value="">Choose content license</option>

                                    @foreach ($licenses as $license)
                                        <option value="{{ $license->id }}"
                                            {{ old('license_id') == $license->id ? 'selected' : '' }}>
                                            {{ $license->name }}
                                        </option>
                                    @endforeach
                                </select>

                                @error('license_id')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror

                                <div class="form-text">
                                    This folder is a collection, so the content uses the license selected here.
                                </div>
                            </div>
                        @endif

                        <div class="mb-3">
                            <label for="price" class="form-label">Content Price (IDR)</label>

                            @if ($parentFolder->is_bundle && !$parentFolder->allow_individual_sale)
                                <input id="price_display" type="number" value="0"
                                    class="form-control @error('price') is-invalid @enderror" disabled>

                                <input type="hidden" id="price" name="price" value="0">

                                <div class="form-text">
                                    This bundle does not allow individual sale, so content price is not used.
                                </div>
                            @else
                                <input id="price" name="price" type="number" min="0" step="0.01"
                                    value="{{ old('price') }}"
                                    class="form-control @error('price') is-invalid @enderror">

                                <div class="form-text">
                                    This price is used when the content can be purchased individually.
                                </div>
                            @endif

                            @error('price')
                                <div class="invalid-feedback d-block">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        {{-- <div class="mb-3">
                            <label for="license_id" class="form-label">License</label>
                            <select class="form-select @error('license_id') is-invalid @enderror" id="license_id"
                                name="license_id" required>
                                @foreach ($licenses as $license)
                                    <option value="{{ $license->id }}"
                                        {{ old('license_id', $parentFolder->license_id) == $license->id ? 'selected' : '' }}>
                                        {{ $license->name }}
                                    </option>
                                @endforeach
                            </select>

                            @error('license_id')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div> --}}

                        <div class="mb-3">
                            <label for="sale_type" class="form-label">Sale Type</label>

                            @if ($parentFolder->is_bundle)
                                <input type="text" class="form-control" value="Multi Sale" disabled>
                                <input type="hidden" id="sale_type" name="sale_type" value="multi_sale">

                                <div class="form-text">
                                    Content inside a bundle folder must use multi-sale.
                                </div>
                            @else
                                <select class="form-select @error('sale_type') is-invalid @enderror" id="sale_type"
                                    name="sale_type" required>
                                    <option value="multi_sale" {{ old('sale_type') === 'multi_sale' ? 'selected' : '' }}>
                                        Multi Sale (content can be sold multiple times)
                                    </option>
                                    <option value="single_sale"
                                        {{ old('sale_type') === 'single_sale' ? 'selected' : '' }}>
                                        Single Sale (content can only be sold once)
                                    </option>
                                </select>
                            @endif

                            @error('sale_type')
                                <div class="invalid-feedback d-block">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="sale_status" class="form-label">Sale Status</label>
                            <select class="form-select @error('sale_status') is-invalid @enderror" id="sale_status"
                                name="sale_status" required>
                                <option value="available"
                                    {{ old('sale_status', 'available') === 'available' ? 'selected' : '' }}>
                                    Available
                                </option>
                                <option value="inactive" {{ old('sale_status') === 'inactive' ? 'selected' : '' }}>
                                    Inactive
                                </option>
                            </select>

                            @error('sale_status')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="tag_name" class="form-label">
                                Pilih Tags
                            </label>
                            <select class="form-select @error('tag_name') is-invalid @enderror" id="tag_name"
                                name="tag_name[]" multiple size="8" required>
                                @foreach ($tags as $tag)
                                    <option value="{{ $tag->id }}"
                                        {{ collect(old('tag_name', []))->contains($tag->id) ? 'selected' : '' }}>
                                        {{ $tag->tag_name }}</option>
                                @endforeach
                            </select>
                            <div class="form-text">
                                Choose at least one tag. Tags are required to help buyers discover related content.
                            </div>
                            @error('tag_name')
                                <div class="invalid-feedback d-block">
                                    {{ $message }}
                                </div>
                            @enderror

                            @error('tag_name.*')
                                <div class="invalid-feedback d-block">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="form-check mb-3">
                            <input class="form-check-input @error('policy_agreement') is-invalid @enderror"
                                type="checkbox" name="policy_agreement" id="policy_agreement" value="1"
                                {{ old('policy_agreement') ? 'checked' : '' }} required>

                            <label class="form-check-label" for="policy_agreement">
                                I have read and agree to the
                                <a href="{{ route('policies.index') }}" target="_blank">
                                    Rupaia Policies
                                </a>,
                                including upload rules, copyright policy, and license terms.
                            </label>

                            @error('policy_agreement')
                                <div class="invalid-feedback d-block">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <button class="w-100 btn btn-lg btn-dark" type="submit">
                            upload
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const input = document.getElementById('path_hi_res');
            const wrapper = document.getElementById('contentPreviewWrapper');
            const image = document.getElementById('contentPreviewImage');
            const fileName = document.getElementById('contentPreviewName');
            const fileSize = document.getElementById('contentPreviewSize');

            function resetPreview() {
                if (!wrapper || !image || !fileName || !fileSize) {
                    return;
                }

                wrapper.classList.add('d-none');
                image.src = '';
                fileName.textContent = '';
                fileSize.textContent = '';
            }

            if (!input) {
                return;
            }

            input.addEventListener('change', function() {
                const file = this.files[0];

                if (!file) {
                    resetPreview();
                    return;
                }

                if (!file.type.startsWith('image/')) {
                    alert('Please choose a valid image file.');
                    this.value = '';
                    resetPreview();
                    return;
                }

                const reader = new FileReader();

                reader.onload = function(event) {
                    image.src = event.target.result;
                    fileName.textContent = file.name;
                    fileName.title = file.name;
                    fileSize.textContent = (file.size / 1024 / 1024).toFixed(2) + ' MB';
                    wrapper.classList.remove('d-none');
                };

                reader.readAsDataURL(file);
            });
        });
    </script>
@endsection
