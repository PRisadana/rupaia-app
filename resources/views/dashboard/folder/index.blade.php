@extends('layouts.main')

@section('content')
    <div class="container my-4">
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        <div class="p-5 text-center bg-body-tertiary rounded-3">
            <img src="{{ Auth::user()->profile_photo_path ? asset('storage/' . Auth::user()->profile_photo_path) : asset('aset/rupaia_logo.png') }}"
                alt="Profile" class="rounded-circle" width="100" height="100" style="object-fit: cover;">
            <h1 class="text-body-emphasis my-3">{{ Auth::user()->name }}</h1>
            <p class="col-lg-8 mx-auto my-3 fs-5 text-muted">
                {{ Auth::user()->bio ?? 'This user has not set a bio yet.' }}
            </p>
            <div class="d-inline-flex gap-2 my-3">
                <button class="d-inline-flex align-items-center btn btn-primary px-4 rounded-pill" type="button">
                    <a class="text-white nav-link" href="{{ route('profile.edit') }}">{{ __('Profile Setting') }}</a>
                </button>
                <button class="d-inline-flex align-items-center btn btn-outline-secondary px-4 rounded-pill" type="button">
                    <a class="text-dark nav-link" href="{{ route('folder.create') }}">{{ __('Add Folder') }}</a>
                </button>
            </div>
        </div>
    </div>

    <ul class="nav nav-underline justify-content-center">
        <li class="nav-item">
            <a class="nav-link" href="{{ route('content.index') }}">Contents</a>
        </li>
        <li class="nav-item">
            <a class="nav-link active" aria-current="page" href="{{ route('folder.index') }}">Folders</a>
        </li>
    </ul>

    <div class="row my-4">
        @foreach ($folders as $folder)
            <div class="col-md-4 mb-4">
                <div class="card">
                    {{-- <img src="{{ asset('storage/' . $content->path_low_res) }}" class="card-img-top"
                        alt="{{ $content->content_title }}"> --}}

                    <div class="card-body">
                        <h5><a href="{{ route('detail.folder.show', $folder) }}"
                                class="card-title">{{ $folder->folder_name }}</a>
                        </h5>
                        <p class="card-text">Description: {{ $folder->folder_description }}</p>
                        {{-- <p class="card-text">Visibility Content: {{ $content->visibility_content }}</p> --}}
                        <div class="card">
                            <div></div>
                        </div>
                        <div class="d-flex flex-row mb-2 my-3">
                            <a href="{{ route('folder.edit', $folder) }}"
                                class="btn btn-sm btn-outline-primary mx-1">Edit</a>

                            <form action="{{ route('folder.destroy', $folder) }} " method="POST"
                                onsubmit="return confirm ('Are you sure for delete this folder?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
    <div class="mt-4 d-flex justify-content-center">
        {{ $folders->links() }}
    </div>
@endsection
