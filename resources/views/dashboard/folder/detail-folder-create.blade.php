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

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <strong>Terjadi kesalahan:</strong>
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('detail.folder.store') }}" enctype="multipart/form-data">
                        @csrf

                        <input type="hidden" name="id_parent" value="{{ $parentFolder?->id }}">
                        <input type="hidden" name="visibility_folder" value="{{ $parentFolder->visibility_folder }}">

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
                            <label for="folder_description" :value="__('Folder Description')" class="form-label">Folder
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
                            <h3 class="form-label">{{ $parentFolder->visibility_folder }}</h3>
                            <div class="form-text">
                                Visibility above follows the folder in use.
                            </div>
                        </div>

                        {{-- <div class="mb-3">
                            <label for="visibility_folder" :value="__('Folder Visibility')" class="form-label">Folder
                                Visibility</label>
                            <select name="visibility_folder" id="visibility_folder"
                                class="form-select @error('visibility_folder') is-invalid @enderror">
                                <option value="private"
                                    {{ old('visibility_folder', 'private') == 'private' ? 'selected' : '' }}>Private
                                </option>
                                <option value="public" {{ old('visibility_folder') == 'public' ? 'selected' : '' }}>Public
                                </option>
                                <option value="by_request" {{ old('visibility_folder') == 'by_request' ? 'selected' : '' }}>
                                    By Request</option>
                            </select>

                            @error('visibility_folder')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div> --}}

                        <button class="w-100 btn btn-lg btn-dark" type="submit">
                            Add Subfolder
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
