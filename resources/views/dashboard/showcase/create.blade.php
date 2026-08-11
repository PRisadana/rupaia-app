@extends('layouts.main')

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-10 ">
            <div class="card shadow-sm mb-4">
                <div class="card-body p-4 p-md-5">
                    <header>
                        <h2 class="card-title mb-3 text-center">
                            {{ __('Upload Your Showcase!') }}
                        </h2>
                    </header>

                    <form method="POST" action="{{ route('showcase.store') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label for="description" :value="__('Showcase Description')" class="form-label">Showcase
                                Description</label>
                            <input id="description" name="description" type="text" value="{{ old('description') }}"
                                class="form-control @error('description') is-invalid @enderror">

                            @error('description')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="custom_path" class="form-label">{{ __('Showcase Image') }}</label>
                            <input id="custom_path" name="custom_path" type="file"
                                class="form-control @error('custom_path') is-invalid @enderror"
                                accept="image/jpeg,image/png,image/jpg">

                            @error('custom_path')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror

                            <div class="form-text">
                                Supported formats: JPG, JPEG, PNG. This image will be displayed as a showcase item.
                            </div>
                        </div>

                        <div id="showcasePreviewWrapper" class="mb-3 d-none">
                            <label class="form-label">Preview</label>
                            <div class="border rounded-3 p-2 bg-light d-inline-block">
                                <img id="showcasePreviewImage" src="" alt="Showcase preview" class="rounded"
                                    style="width: 160px; object-fit: cover;">
                                <div id="showcasePreviewName" class="small text-muted mt-2 text-truncate"
                                    style="max-width: 160px;"></div>
                                <div id="showcasePreviewSize" class="small text-muted"></div>
                            </div>
                        </div>

                        <div class="form-check mb-3">
                            <input class="form-check-input @error('policy_agreement') is-invalid @enderror" type="checkbox"
                                name="policy_agreement" id="policy_agreement" value="1"
                                {{ old('policy_agreement') ? 'checked' : '' }} required>

                            <label class="form-check-label" for="policy_agreement">
                                I confirm that this showcase item follows the
                                <a href="{{ route('policies.index') }}" target="_blank">
                                    Rupaia Policies
                                </a>.
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
            const input = document.getElementById('custom_path');
            const wrapper = document.getElementById('showcasePreviewWrapper');
            const image = document.getElementById('showcasePreviewImage');
            const fileName = document.getElementById('showcasePreviewName');
            const fileSize = document.getElementById('showcasePreviewSize');

            if (!input) {
                return;
            }

            input.addEventListener('change', function() {
                const file = this.files[0];

                if (!file) {
                    wrapper.classList.add('d-none');
                    image.src = '';
                    fileName.textContent = '';
                    fileSize.textContent = '';
                    return;
                }

                if (!file.type.startsWith('image/')) {
                    alert('Please choose a valid image file.');
                    this.value = '';
                    wrapper.classList.add('d-none');
                    image.src = '';
                    fileName.textContent = '';
                    fileSize.textContent = '';
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
