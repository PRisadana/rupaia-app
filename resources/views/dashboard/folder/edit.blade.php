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

                    <form method="POST" action="{{ route('folder.update', $folder) }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

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
                                <option value="public" {{ old('visibility') == 'public' ? 'selected' : '' }}>Public
                                </option>
                                <option value="private" {{ old('visibility', 'private') == 'private' ? 'selected' : '' }}>
                                    Private
                                </option>
                                <option value="by_request" {{ old('visibility') == 'by_request' ? 'selected' : '' }}>
                                    By Request</option>
                            </select>

                            @error('visibility')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="is_bundle" :value="__('Is Bundle')" class="form-label">Is this folder a
                                bundle?</label>
                            <select name="is_bundle" id="is_bundle"
                                class="form-select @error('is_bundle') is-invalid @enderror">
                                <option value="1" {{ old('is_bundle') == '1' ? 'selected' : '' }}>Yes
                                </option>
                                <option value="0" {{ old('is_bundle', '0') == '0' ? 'selected' : '' }}>
                                    No
                                </option>
                            </select>

                            @error('is_bundle')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="bundle_price" :value="__('Bundle Price')" class="form-label">Bundle Price
                                (IDR)</label>
                            <input id="bundle_price" name="bundle_price" type="number"
                                value="{{ old('bundle_price', $folder->bundle_price) }}"
                                class="form-control @error('bundle_price') is-invalid @enderror">

                            @error('bundle_price')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <button class="w-100 btn btn-lg btn-dark" type="submit">
                            Update Folder
                        </button>
                    </form>
                </div>
            </div>
        </div>
    @endsection
