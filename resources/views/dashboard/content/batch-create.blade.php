@extends('layouts.main')

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-11">
            <div class="card shadow-sm mb-4">
                <div class="card-body p-4 p-md-5">
                    <header class="mb-4">
                        <h2 class="card-title mb-2 text-center">Batch Upload Content</h2>
                        <p class="text-muted text-center mb-0">
                            Upload multiple images at once. Maximum 50 files per batch.
                        </p>
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

                    <form method="POST" action="{{ route('content.batch.store') }}" enctype="multipart/form-data">
                        @csrf

                        <div class="mb-3">
                            <label for="folder_id" class="form-label">Destination Folder</label>
                            <select id="folder_id" name="folder_id"
                                class="form-select @error('folder_id') is-invalid @enderror" required>
                                <option value="">Choose folder</option>

                                @foreach ($folders as $folder)
                                    <option value="{{ $folder->id }}" data-is-bundle="{{ (int) $folder->is_bundle }}"
                                        data-license-name="{{ $folder->license->name ?? '' }}"
                                        data-license-id="{{ $folder->license_id ?? '' }}"
                                        data-allow-individual-sale="{{ (int) $folder->allow_individual_sale }}"
                                        {{ old('folder_id') == $folder->id ? 'selected' : '' }}>
                                        {{ $folder->folder_name }}
                                        — {{ $folder->is_bundle ? 'Bundle' : 'Collection' }}
                                    </option>
                                @endforeach
                            </select>

                            @error('folder_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror

                            <div id="folderRuleInfo" class="form-text">
                                Choose a folder to see upload rules.
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="images" class="form-label">Images</label>
                            <input id="images" name="images[]" type="file"
                                class="form-control @error('images') is-invalid @enderror @error('images.*') is-invalid @enderror"
                                accept="image/jpeg,image/png,image/jpg" multiple required>

                            @error('images')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror

                            @error('images.*')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror

                            <div class="form-text">
                                Maximum 50 images per batch. Supported formats: JPG, JPEG, PNG. Maximum size: 10 MB per
                                file.
                            </div>
                        </div>

                        <div id="previewAlert" class="alert alert-light border d-none">
                            <strong id="previewCount">0 files selected.</strong>
                            <div class="small text-muted mt-1">
                                Preview is shown to help you confirm the selected files before upload.
                            </div>
                        </div>

                        <div id="previewGrid" class="row g-3 mb-4"></div>

                        <div class="mb-3">
                            <label for="default_description" class="form-label">Default Description</label>
                            <textarea id="default_description" name="default_description" rows="3"
                                class="form-control @error('default_description') is-invalid @enderror">{{ old('default_description') }}</textarea>

                            @error('default_description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror

                            <div class="form-text">
                                This description will be applied to all uploaded contents. You can edit each content later.
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="price" class="form-label">Default Price (IDR)</label>
                            <input id="price" name="price" type="number" min="0" step="0.01"
                                class="form-control @error('price') is-invalid @enderror" value="{{ old('price') }}">

                            @error('price')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror

                            <div class="form-text">
                                This price will be applied to all uploaded contents.
                            </div>
                        </div>

                        <div id="collectionLicenseField" class="mb-3">
                            <label for="license_id" class="form-label">Default Content License</label>
                            <select id="license_id" name="license_id"
                                class="form-select @error('license_id') is-invalid @enderror">
                                <option value="">Choose license</option>

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

                            <div class="form-text">
                                This license will be applied to all contents uploaded to a collection folder.
                            </div>
                        </div>

                        <div id="bundleLicenseInfo" class="mb-3 d-none">
                            <label class="form-label">Bundle License</label>
                            <input id="bundleLicenseName" type="text" class="form-control" disabled>

                            <div class="form-text">
                                This folder is a bundle. All uploaded contents will automatically follow the bundle license.
                            </div>
                        </div>

                        <div id="saleTypeField" class="mb-3">
                            <label for="sale_type" class="form-label">Default Sale Type</label>
                            <select id="sale_type" name="sale_type"
                                class="form-select @error('sale_type') is-invalid @enderror">
                                <option value="multi_sale" {{ old('sale_type') === 'multi_sale' ? 'selected' : '' }}>
                                    Multi Sale
                                </option>
                                <option value="single_sale" {{ old('sale_type') === 'single_sale' ? 'selected' : '' }}>
                                    Single Sale
                                </option>
                            </select>

                            @error('sale_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror

                            <div class="form-text">
                                This sale type will be applied to all uploaded contents in a collection folder.
                            </div>
                        </div>

                        <div id="bundleSaleTypeInfo" class="mb-3 d-none">
                            <label class="form-label">Sale Type</label>
                            <input type="text" class="form-control" value="Multi Sale" disabled>
                            <input type="hidden" id="bundle_sale_type" value="multi_sale">

                            <div class="form-text">
                                Content inside a bundle folder must use multi-sale.
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="sale_status" class="form-label">Default Sale Status</label>
                            <select id="sale_status" name="sale_status"
                                class="form-select @error('sale_status') is-invalid @enderror" required>
                                <option value="available"
                                    {{ old('sale_status', 'available') === 'available' ? 'selected' : '' }}>
                                    Available
                                </option>
                                <option value="inactive" {{ old('sale_status') === 'inactive' ? 'selected' : '' }}>
                                    Inactive
                                </option>
                            </select>

                            @error('sale_status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="tag_name" class="form-label">Default Tags</label>
                            <select id="tag_name" name="tag_name[]" multiple size="8" required
                                class="form-select @error('tag_name') is-invalid @enderror">
                                @foreach ($tags as $tag)
                                    <option value="{{ $tag->id }}"
                                        {{ collect(old('tag_name', []))->contains($tag->id) ? 'selected' : '' }}>
                                        {{ $tag->tag_name }}
                                    </option>
                                @endforeach
                            </select>

                            @error('tag_name')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror

                            <div class="form-text">
                                Choose at least one tag. Tags are required to help buyers discover related content.
                            </div>
                        </div>

                        <div class="form-check mb-4">
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
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <button class="w-100 btn btn-lg btn-dark" type="submit">
                            Upload Batch
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const maxFiles = 50;

            const folderSelect = document.getElementById('folder_id');
            const folderRuleInfo = document.getElementById('folderRuleInfo');
            const collectionLicenseField = document.getElementById('collectionLicenseField');
            const bundleLicenseInfo = document.getElementById('bundleLicenseInfo');
            const bundleLicenseName = document.getElementById('bundleLicenseName');
            const saleTypeField = document.getElementById('saleTypeField');
            const bundleSaleTypeInfo = document.getElementById('bundleSaleTypeInfo');
            const priceInput = document.getElementById('price');

            const imagesInput = document.getElementById('images');
            const previewGrid = document.getElementById('previewGrid');
            const previewAlert = document.getElementById('previewAlert');
            const previewCount = document.getElementById('previewCount');

            function updateFolderRules() {
                if (!folderSelect) {
                    return;
                }

                const selected = folderSelect.options[folderSelect.selectedIndex];

                if (!selected || !selected.value) {
                    if (folderRuleInfo) {
                        folderRuleInfo.textContent = 'Choose a folder to see upload rules.';
                    }

                    if (collectionLicenseField) {
                        collectionLicenseField.classList.remove('d-none');
                    }

                    if (bundleLicenseInfo) {
                        bundleLicenseInfo.classList.add('d-none');
                    }

                    if (saleTypeField) {
                        saleTypeField.classList.remove('d-none');
                    }

                    if (bundleSaleTypeInfo) {
                        bundleSaleTypeInfo.classList.add('d-none');
                    }

                    if (priceInput) {
                        priceInput.disabled = false;
                    }

                    return;
                }

                const isBundle = selected.dataset.isBundle === '1';
                const licenseName = selected.dataset.licenseName || 'Bundle license not set';
                const allowIndividualSale = selected.dataset.allowIndividualSale === '1';

                if (isBundle) {
                    if (folderRuleInfo) {
                        folderRuleInfo.textContent =
                            allowIndividualSale ?
                            'This is a bundle folder. License and sale type will follow bundle rules. Individual sale is allowed, so price can still be used.' :
                            'This is a bundle-only folder. License and sale type will follow bundle rules. Price will be set to 0.';
                    }

                    if (collectionLicenseField) {
                        collectionLicenseField.classList.add('d-none');
                    }

                    if (bundleLicenseInfo) {
                        bundleLicenseInfo.classList.remove('d-none');
                    }

                    if (bundleLicenseName) {
                        bundleLicenseName.value = licenseName;
                    }

                    if (saleTypeField) {
                        saleTypeField.classList.add('d-none');
                    }

                    if (bundleSaleTypeInfo) {
                        bundleSaleTypeInfo.classList.remove('d-none');
                    }

                    if (priceInput) {
                        if (allowIndividualSale) {
                            priceInput.disabled = false;
                        } else {
                            priceInput.value = 0;
                            priceInput.disabled = true;
                        }
                    }

                    return;
                }

                if (folderRuleInfo) {
                    folderRuleInfo.textContent =
                        'This is a collection folder. Each uploaded content will use the selected default content license, price, and sale type.';
                }

                if (collectionLicenseField) {
                    collectionLicenseField.classList.remove('d-none');
                }

                if (bundleLicenseInfo) {
                    bundleLicenseInfo.classList.add('d-none');
                }

                if (saleTypeField) {
                    saleTypeField.classList.remove('d-none');
                }

                if (bundleSaleTypeInfo) {
                    bundleSaleTypeInfo.classList.add('d-none');
                }

                if (priceInput) {
                    priceInput.disabled = false;
                }
            }

            function resetPreview() {
                if (!previewGrid || !previewAlert || !previewCount) {
                    return;
                }

                previewGrid.innerHTML = '';
                previewAlert.classList.add('d-none');
                previewCount.textContent = '0 files selected.';
            }

            function createPreview(file) {
                const reader = new FileReader();

                reader.onload = function(event) {
                    const col = document.createElement('div');
                    col.className = 'col-6 col-md-3 col-lg-2';

                    col.innerHTML = `
                    <div class="border rounded-3 p-2 h-100 bg-light">
                        <img src="${event.target.result}"
                            class="img-fluid rounded mb-2"
                            style="width: 100%; height: 120px; object-fit: cover;"
                            alt="Preview">
                        <div class="small text-truncate" title="${file.name}">
                            ${file.name}
                        </div>
                        <div class="small text-muted">
                            ${(file.size / 1024 / 1024).toFixed(2)} MB
                        </div>
                    </div>
                `;

                    previewGrid.appendChild(col);
                };

                reader.readAsDataURL(file);
            }

            if (imagesInput) {
                imagesInput.addEventListener('change', function() {
                    resetPreview();

                    const files = Array.from(this.files);

                    if (files.length === 0) {
                        return;
                    }

                    if (files.length > maxFiles) {
                        alert(`Maximum ${maxFiles} files are allowed per batch.`);
                        this.value = '';
                        resetPreview();
                        return;
                    }

                    previewAlert.classList.remove('d-none');
                    previewCount.textContent = `${files.length} file(s) selected.`;

                    files.forEach(function(file) {
                        if (!file.type.startsWith('image/')) {
                            return;
                        }

                        createPreview(file);
                    });
                });
            }

            if (folderSelect) {
                folderSelect.addEventListener('change', updateFolderRules);
                updateFolderRules();
            }

            const form = folderSelect ? folderSelect.closest('form') : null;

            if (form) {
                form.addEventListener('submit', function() {
                    if (priceInput) {
                        priceInput.disabled = false;
                    }
                });
            }
        });
    </script>
@endsection
