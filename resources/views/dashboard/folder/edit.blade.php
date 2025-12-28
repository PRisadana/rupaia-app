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
                            <label for="visibility_folder" :value="__('Folder Visibility')" class="form-label">Folder
                                Visibility</label>
                            <select name="visibility_folder" id="visibility_folder"
                                class="form-select @error('visibility_folder') is-invalid @enderror">
                                <option value="public" {{ old('visibility_folder') == 'public' ? 'selected' : '' }}>Public
                                    (default)
                                </option>
                                <option value="private"
                                    {{ old('visibility_folder', 'private') == 'private' ? 'selected' : '' }}>Private
                                </option>
                                <option value="by_request" {{ old('visibility_folder') == 'by_request' ? 'selected' : '' }}>
                                    By Request</option>
                            </select>

                            @error('visibility_folder')
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
