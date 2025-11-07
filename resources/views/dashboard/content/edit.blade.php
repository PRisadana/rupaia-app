@extends('layouts.main')

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-10 ">
            <div class="card shadow-sm mb-4">
                <div class="card-body p-4 p-md-5">
                    <header>
                        <h2 class="card-title mb-3 text-center">
                            {{ __('Edit Your Content!') }}
                        </h2>
                    </header>

                    <form method="POST" action="{{ route('content.update', $content) }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="mb-3 text-center">
                            <img src="{{ asset('storage/' . $content->path_low_res) }}" class="img-thumbnail"
                                style="max-width: 300px; max-height: 300px; object-fit: cover;"
                                alt="{{ $content->content_title }}">
                        </div>

                        <div class="mb-3">
                            <label for="content_title" :value="__('Content Title')" class="form-label">Content Title</label>
                            <input id="content_title" name="content_title" type="text"
                                value="{{ old('content_title', $content->content_title) }}"
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
                                value="{{ old('content_description', $content->content_description) }}"
                                class="form-control
                                @error('content_description') is-invalid @enderror">

                            @error('content_description')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="id_folder" class="form-label">Pilih Folder</label>
                            <div class="input-group">
                                <select class="form-select @error('id_folder') is-invalid @enderror" id="id_folder"
                                    name="id_folder" required>
                                    @foreach ($folders as $folder)
                                        <option value="{{ $folder->id }}"
                                            {{ old('id_folder', $content->id_folder) == $folder->id ? 'selected' : '' }}>
                                            {{ $folder->folder_name }}
                                        </option>
                                    @endforeach
                                </select>
                                <button class="btn btn-outline-secondary" type="button" data-bs-toggle="modal"
                                    data-bs-target="#tambahFolderModal">
                                    Buat Folder Baru
                                </button>
                                @error('id_folder')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="visibility_content" class="form-label">Visibilitas</label>
                            <select class="form-select" id="visibility_content" name="visibility_content" required>
                                <option value="public"
                                    {{ old('visibility_content', $content->visibility_content) == 'public' ? 'selected' : '' }}>
                                    Public (Default, semua orang bisa lihat)</option>
                                <option value="private"
                                    {{ old('visibility_content', $content->visibility_content) == 'private' ? 'selected' : '' }}>
                                    Private (Hanya Anda yang lihat)</option>
                                <option value="by_request"
                                    {{ old('visibility_content', $content->visibility_content) == 'by_request' ? 'selected' : '' }}>
                                    By Request (Pengguna lain harus meminta izin)</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="name_tag" class="form-label">Pilih Tags
                                satu</label>
                            @php
                                $selectedTagIDs = old('name_tag', $content->tags->pluck('id')->all());
                            @endphp
                            <select class="form-select @error('name_tag.*') is-invalid @enderror" id="name_tag"
                                name="name_tag[]" multiple size="8">
                                @foreach ($tags as $tag)
                                    <option value="{{ $tag->id }}"
                                        {{ in_array($tag->id, $selectedTagIDs) ? 'selected' : '' }}>
                                        {{ $tag->name_tag }}
                                    </option>
                                @endforeach
                            </select>
                            @error('name_tag.*')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <button class="w-100 btn btn-lg btn-dark" type="submit">
                            Update Content
                        </button>
                    </form>
                </div>
            </div>
        </div>
    @endsection
