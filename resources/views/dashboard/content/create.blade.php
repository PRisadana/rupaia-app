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

                    <form method="POST" action="{{ route('content.store') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label for="content_title" :value="__('Content Title')" class="form-label">Content Title</label>
                            <input id="content_title" name="content_title" type="text"
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
                                class="form-control @error('path_hi_res') is-invalid @enderror">
                            @error('path_hi_res')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="visibility_content" class="form-label">Visibilitas</label>
                            <select class="form-select" id="visibility_content" name="visibility_content" required>
                                <option value="public" selected>Public (Default, semua orang bisa lihat)</option>
                                <option value="private">Private (Hanya Anda yang lihat)</option>
                                <option value="by_request">By Request (Pengguna lain harus meminta izin)</option>
                            </select>
                            <div class="form-text">
                                Pilih siapa yang dapat melihat konten ini.
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="id_folder" class="form-label">Pilih Folder</label>
                            <div class="input-group">
                                <select class="form-select @error('id_folder') is-invalid @enderror" id="id_folder"
                                    name="id_folder" required>
                                    <option value="" disabled selected>Wajib Pilih Folder</option>
                                    @foreach ($folders as $folder)
                                        <option value="{{ $folder->id }}">{{ $folder->folder_name }}</option>
                                    @endforeach
                                </select>


                                {{-- <button class="btn btn-outline-secondary" type="button" data-bs-toggle="modal"
                                    data-bs-target="#tambahFolderModal">
                                    Buat Folder Baru
                                </button> --}}

                                @error('id_folder')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                            <div class="form-text">
                                Folder di atas adalah folder utama
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="name_tag" class="form-label">
                                Pilih Tags
                            </label>
                            <select class="form-select @error('name_tag') is-invalid @enderror" id="name_tag"
                                name="name_tag[]" multiple size="8">
                                @foreach ($tags as $tag)
                                    <option value="{{ $tag->id }}">{{ $tag->name_tag }}</option>
                                @endforeach
                            </select>
                            <div class="form-text">
                                Ini adalah daftar tag yang sudah disetujui Admin.
                            </div>
                            @error('name_tag')
                                <div class="invalid-feedback d-block">
                                    {{ $message }}
                                </div>
                            @enderror

                            @error('name_tag.*')
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

    {{-- <div class="modal fade" id="tambahFolderModal" tabindex="-1" aria-labelledby="tambahFolderModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">

                <form action="{{ route('folder.store') }}" method="POST">
                    @csrf <div class="modal-header">
                        <h5 class="modal-title" id="tambahFolderModalLabel">Buat Folder Baru</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">

                        <div class="mb-3">
                            <label for="folder_name" class="form-label">Nama Folder</label>
                            <input type="text" class="form-control" name="folder_name" id="folder_name" required>
                        </div>

                        <div class="mb-3">
                            <label for="folder_description" class="form-label">Deskripsi (Opsional)</label>
                            <textarea class="form-control" name="folder_description" id="folder_description" rows="2"></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="id_parent" class="form-label">Sub-folder dari (Opsional)</label>
                            <select class="form-select" name="id_parent" id="id_parent">
                                <option value="">Jadikan Folder Utama</option>
                                @foreach ($folders as $folder)
                                    <option value="{{ $folder->id }}">{{ $folder->folder_name }}</option>
                                @endforeach
                            </select>
                        </div>

                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan Folder</button>
                    </div> --}}

    </form>
    </div>
    </div>
    </div>
@endsection
