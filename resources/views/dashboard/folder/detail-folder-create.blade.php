@extends('layouts.main')

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-10 ">
            <div class="card shadow-sm mb-4">
                <div class="card-body p-4 p-md-5">
                    <header>
                        <h2 class="card-title mb-3 text-center">
                            {{ __('Add Your Folder!') }}
                        </h2>
                    </header>

                    <form method="POST" action="{{ route('detail.folder.store') }}" enctype="multipart/form-data">
                        @csrf

                        <input type="hidden" name="id_folder" value="{{ $parentFolder?->id }}">

                        <div class="mb-3">
                            <h3 class="form-label">{{ $parentFolder->folder_name }}</h3>
                            <div class="form-text">
                                Folder di atas adalah folder root sebelumnya
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

                        <button class="w-100 btn btn-lg btn-dark" type="submit">
                            Add Folder
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
