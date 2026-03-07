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
                        <input type="hidden" name="is_bundle" value="{{ $parentFolder->is_bundle }}">

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
                            <h3 class="form-label">{{ $parentFolder->is_bundle ? 'Yes' : 'No' }}</h3>
                            <div class="form-text">
                                The folder above is the previous bundle folder.
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="bundle_price" :value="__('Bundle Price')" class="form-label">Bundle Price
                                (IDR)</label>
                            <input id="bundle_price" name="bundle_price" type="number"
                                placeholder="Only fill if this folder is a bundle"
                                class="form-control @error('bundle_price') is-invalid @enderror">

                            @error('bundle_price')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
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
