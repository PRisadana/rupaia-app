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

                    <form method="POST" action="{{ route('content.store') }}" enctype="multipart/form-data">
                        @csrf
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
                                Supported formats: JPG, JPEG, PNG. Maximum size follows system upload rules.
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

                        <div class="mb-3">
                            <label for="folder_id" class="form-label">Choose Folder</label>
                            <div class="input-group">
                                <select class="form-select @error('folder_id') is-invalid @enderror" id="folder_id"
                                    name="folder_id" required>
                                    <option value="" disabled selected>Must choose Folder</option>
                                    @foreach ($folders as $folder)
                                        <option value="{{ $folder->id }}" data-visibility="{{ $folder->visibility }}"
                                            data-is-bundle="{{ (int) $folder->is_bundle }}"
                                            data-license-id="{{ $folder->license_id ?? '' }}"
                                            data-license-name="{{ $folder->license->name ?? '' }}"
                                            data-allow-individual-sale="{{ (int) $folder->allow_individual_sale }}"
                                            {{ old('folder_id') == $folder->id ? 'selected' : '' }}>
                                            {{ $folder->folder_name }}
                                            — {{ $folder->is_bundle ? 'Bundle' : 'Collection' }}
                                            — {{ $folder->visibility }}
                                        </option>
                                    @endforeach
                                </select>

                                @error('folder_id')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                        </div>

                        <div id="folderBusinessRuleInfo" class="alert alert-light border d-none">
                            <div id="folderBusinessRuleText" class="small"></div>
                        </div>

                        <div class="mb-3">
                            <label for="price" class="form-label">
                                Content Price (IDR)
                            </label>

                            <input id="price" name="price" type="number" min="0" step="0.01"
                                value="{{ old('price') }}" class="form-control @error('price') is-invalid @enderror">

                            @error('price')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror

                            <div id="priceHelpText" class="form-text">
                                This price is used when the content can be purchased individually.
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="license_id" :value="__('License')" class="form-label">Content License
                            </label>
                            <select id="license_id" name="license_id"
                                class="form-select @error('license_id') is-invalid @enderror" required>
                                <option value="">Select a License</option>
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
                                If the selected folder is a bundle, this content will automatically use the bundle folder
                                license.
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="sale_type" class="form-label">Sale Type</label>
                            <div class="input-group">
                                <select class="form-select @error('sale_type') is-invalid @enderror" id="sale_type"
                                    name="sale_type" required>
                                    <option value="multi_sale" {{ old('sale_type') === 'multi_sale' ? 'selected' : '' }}>
                                        Multi Sale (content can be sold multiple times)</option>
                                    <option value="single_sale"
                                        {{ old('sale_type') === 'single_sale' ? 'selected' : '' }}>
                                        Single Sale (content can only be sold once)</option>
                                </select>

                                @error('sale_type')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="sale_status" class="form-label">Sale Status</label>
                            <div class="input-group">
                                <select class="form-select @error('sale_status') is-invalid @enderror" id="sale_status"
                                    name="sale_status" required>
                                    <option value="available">Available</option>
                                    <option value="inactive">Inactive</option>
                                </select>

                                @error('sale_status')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="tag_name" class="form-label">
                                Choose Tags
                            </label>
                            <select class="form-select @error('tag_name') is-invalid @enderror" id="tag_name"
                                name="tag_name[]" multiple size="8" required>
                                @foreach ($tags as $tag)
                                    <option value="{{ $tag->id }}"
                                        {{ collect(old('tag_name', []))->contains($tag->id) ? 'selected' : '' }}>
                                        {{ $tag->tag_name }}
                                    </option>
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
            const imageInput = document.getElementById('path_hi_res');
            const previewWrapper = document.getElementById('contentPreviewWrapper');
            const previewImage = document.getElementById('contentPreviewImage');
            const previewName = document.getElementById('contentPreviewName');
            const previewSize = document.getElementById('contentPreviewSize');

            const folderSelect = document.getElementById('folder_id');
            const licenseSelect = document.getElementById('license_id');
            const saleTypeSelect = document.getElementById('sale_type');
            const priceInput = document.getElementById('price');
            const priceHelpText = document.getElementById('priceHelpText');

            const infoBox = document.getElementById('folderBusinessRuleInfo');
            const infoText = document.getElementById('folderBusinessRuleText');

            function resetPreview() {
                if (!previewWrapper || !previewImage || !previewName || !previewSize) {
                    return;
                }

                previewWrapper.classList.add('d-none');
                previewImage.src = '';
                previewName.textContent = '';
                previewSize.textContent = '';
            }

            function handleImagePreview() {
                if (!imageInput) {
                    return;
                }

                imageInput.addEventListener('change', function() {
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
                        previewImage.src = event.target.result;
                        previewName.textContent = file.name;
                        previewName.title = file.name;
                        previewSize.textContent = (file.size / 1024 / 1024).toFixed(2) + ' MB';
                        previewWrapper.classList.remove('d-none');
                    };

                    reader.readAsDataURL(file);
                });
            }

            function applyFolderBusinessRule() {
                if (!folderSelect || !licenseSelect || !saleTypeSelect || !priceInput) {
                    return;
                }

                const selected = folderSelect.options[folderSelect.selectedIndex];

                if (!selected || !selected.value) {
                    licenseSelect.disabled = false;
                    saleTypeSelect.disabled = false;
                    priceInput.disabled = false;

                    if (priceHelpText) {
                        priceHelpText.textContent =
                            'This price is used when the content can be purchased individually.';
                    }

                    if (infoBox) {
                        infoBox.classList.add('d-none');
                    }

                    return;
                }

                const isBundle = selected.dataset.isBundle === '1';
                const licenseId = selected.dataset.licenseId;
                const licenseName = selected.dataset.licenseName || 'bundle license';
                const allowIndividualSale = selected.dataset.allowIndividualSale === '1';

                if (isBundle) {
                    if (licenseId) {
                        licenseSelect.value = licenseId;
                    }

                    licenseSelect.disabled = true;
                    saleTypeSelect.value = 'multi_sale';
                    saleTypeSelect.disabled = true;

                    if (allowIndividualSale) {
                        priceInput.disabled = false;

                        if (priceHelpText) {
                            priceHelpText.textContent =
                                'This bundle allows individual sale, so content price can be used for individual purchase.';
                        }

                        if (infoText) {
                            infoText.textContent =
                                `This is a bundle folder. Content license will follow ${licenseName}, sale type will be Multi Sale, and content can still be sold individually.`;
                        }
                    } else {
                        priceInput.value = 0;
                        priceInput.disabled = true;

                        if (priceHelpText) {
                            priceHelpText.textContent =
                                'This bundle does not allow individual sale, so content price is not used. The content is only available through the bundle.';
                        }

                        if (infoText) {
                            infoText.textContent =
                                `This is a bundle-only folder. Content license will follow ${licenseName}, sale type will be Multi Sale, and content cannot be sold individually.`;
                        }
                    }

                    if (infoBox) {
                        infoBox.classList.remove('d-none');
                    }

                    return;
                }

                licenseSelect.disabled = false;
                saleTypeSelect.disabled = false;
                priceInput.disabled = false;

                if (priceHelpText) {
                    priceHelpText.textContent =
                        'This folder is a collection, so the content uses its own price, license, and sale type.';
                }

                if (infoText) {
                    infoText.textContent =
                        'This is a collection folder. The content can use the selected price, license, and sale type.';
                }

                if (infoBox) {
                    infoBox.classList.remove('d-none');
                }
            }

            handleImagePreview();

            if (folderSelect) {
                folderSelect.addEventListener('change', applyFolderBusinessRule);
                applyFolderBusinessRule();
            }

            const form = folderSelect ? folderSelect.closest('form') : null;

            if (form) {
                form.addEventListener('submit', function() {
                    if (licenseSelect) {
                        licenseSelect.disabled = false;
                    }

                    if (saleTypeSelect) {
                        saleTypeSelect.disabled = false;
                    }

                    if (priceInput) {
                        priceInput.disabled = false;
                    }
                });
            }
        });
    </script>
@endsection
