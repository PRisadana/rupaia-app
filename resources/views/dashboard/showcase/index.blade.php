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
            <div class="d-inline-flex gap-2 my-4">
                <button class="d-inline-flex align-items-center btn btn-dark px-4 rounded-pill" type="button">
                    <a class="text-white nav-link" href="{{ route('profile.edit') }}">{{ __('Profile Setting') }}</a>
                </button>
                <button class="d-inline-flex align-items-center btn btn-outline-secondary px-4 rounded-pill" type="button">
                    <a class="text-dark nav-link" href="{{ route('showcase.create') }}">{{ __('Upload Showcase') }}</a>
                </button>
                <button class="d-inline-flex align-items-center btn btn-outline-secondary px-4 rounded-pill" type="button">
                    <a class="text-dark nav-link"
                        href="{{ route('showcase.from.content.create') }}">{{ __('Add from Content') }}</a>
                </button>
            </div>
        </div>
    </div>

    <ul class="nav nav-underline justify-content-center">
        <li class="nav-item">
            <a class="nav-link active" aria-current="page" href="{{ route('showcase.index') }}">Showcases</a>
        </li>
        <li class="nav-item">
            <a class="nav-link text-secondary" aria-current="page" href="{{ route('content.index') }}">Contents</a>
        </li>
        <li class="nav-item">
            <a class="nav-link text-secondary" href="{{ route('folder.index') }}">Folders</a>
        </li>
    </ul>

    <div class="row my-4">
        @foreach ($showcaseItems as $showcaseItem)
            <div class="col-md-4 mb-4">
                <div class="card">
                    @php
                        $imagePath = $showcaseItem->custom_path ?? $showcaseItem->content?->path_low_res;
                    @endphp

                    <img src="{{ $imagePath ? asset('storage/' . $imagePath) : asset('aset/rupaia_logo.png') }}"
                        class="card-img-top" alt="{{ $showcaseItem->content?->content_title }}">

                    <div class="card-body">
                        <p class="card-text">Description: {{ $showcaseItem->description ?? 'No caption available.' }}</p>
                        <p class="card-text">Item Source: {{ $showcaseItem->item_source }}</p>
                        <p class="card-text">Status: {{ $showcaseItem->status }}</p>

                        <div class="d-flex flex-row mb-2 my-3">
                            <a href="{{ route('showcase.edit', $showcaseItem) }}" class="btn btn-sm btn-secondary mx-1"><i
                                    class="fi fi-rr-edit"></i></a>
                            <form action="{{ route('showcase.destroy', $showcaseItem) }}" method="POST"
                                onsubmit="return confirm ('Are you sure for delete this content?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger mx-1"><i
                                        class="fi fi-rr-trash"></i></button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
    <div class="my-2">
        {{ $showcaseItems->links() }}
    </div>
@endsection
