@extends('layouts.main')

@section('content')
    <div class="container my-5">
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
                <button class="d-inline-flex align-items-center btn btn-primary btn-lg px-4 rounded-pill" type="button">
                    <a class="text-white nav-link" href="{{ route('profile.edit') }}">{{ __('Profile Setting') }}</a>
                </button>
                <button class="btn btn-outline-secondary btn-lg px-4 rounded-pill" type="button">
                    <a class="nav-link text-dark" href="{{ route('content.create') }}">Upload</a>
                </button>
            </div>
        </div>
    </div>

    <div class="row">
        @foreach ($contents as $content)
            <div class="col-md-4 mb-4">
                <div class="card">
                    <img src="{{ asset('storage/' . $content->path_low_res) }}" class="card-img-top"
                        alt="{{ $content->content_title }}">

                    <div class="card-body">
                        <h5 class="card-title">{{ $content->content_title }}</h5>
                        <p class="card-text">Description: {{ $content->content_description }}</p>
                        <p class="card-text">Folder: {{ $content->folder->folder_name }}</p>
                        <p class="card-text">
                            Tags:
                            @foreach ($content->tags as $tag)
                                <span class="badge bg-secondary">{{ $tag->name_tag }}</span>
                            @endforeach
                        </p>
                        <div col-4>

                        </div>
                        <div class="card">
                            <div></div>
                        </div>
                        <div class="d-flex flex-row mb-2 my-3">
                            <a href="{{ route('content.edit', $content) }}"
                                class="btn btn-sm btn-outline-primary mx-1">Edit</a>

                            <form action="{{ route('content.destroy', $content) }}" method="POST"
                                onsubmit="return confirm ('Are you sure for delete this content?')">
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
        {{ $contents->links() }}
    </div>
@endsection
