@extends('layouts.main')

@section('content')
    <div class="container my-4">
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <a href="{{ route('authors.show', $folder->user->id) }}"
            class="d-inline-flex align-items-center text-decoration-none gap-2">
            <img src="{{ $folder->user->profile_photo_path ? asset('storage/' . $folder->user->profile_photo_path) : asset('aset/rupaia_logo.png') }}"
                alt="Profile" class="rounded-circle flex-shrink-0" width="32" height="32"
                style="object-fit: cover;">
            <h5 class="text-muted m-0 fs-6 fw-semibold"> {{ $folder->user->name }}'s Published Folder</h5>
        </a>

        <h2><strong>{{ $folder->folder_name }}</strong></h2>
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb">
                @foreach ($breadcrumbs as $crumb)
                    <li class="breadcrumb-item {{ $loop->last ? 'active' : '' }}">
                        @if ($loop->last)
                            {{ $crumb->folder_name }}
                        @else
                            <a href="{{ route('folder.show', $crumb) }}">{{ $crumb->folder_name }}</a>
                        @endif
                    </li>
                @endforeach
            </ol>
        </nav>


        <div class="row my-4">
            @foreach ($subfolders as $subfolder)
                <div class="col-md-4 mb-4">
                    <div class="card">
                        {{-- <img src="{{ asset('storage/' . $content->path_low_res) }}" class="card-img-top"
                        alt="{{ $content->content_title }}"> --}}

                        <div class="card-body">
                            <h5><a href="{{ route('folder.show', $subfolder) }}"
                                    class="card-title">{{ $subfolder->folder_name }}</a>
                            </h5>
                            <p class="card-text">Description: {{ $subfolder->folder_description }}</p>
                            <p class="card-text"> <strong>Rp
                                    {{ number_format($subfolder->bundle_price, 0, ',', '.') }}</strong>
                            </p>
                        </div>
                        <div class="d-flex flex-column flex-md-row gap-2">
                            <button class="btn btn-dark flex-grow-1 d-flex align-items-center justify-content-center gap-2"
                                type="submit">
                                <i class="fi fi-rr-shopping-cart-add"></i>
                                <span>Add to Cart</span>
                            </button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        @php
            $itemCount = $contents->count();
            $masonryClass = $itemCount < 4 ? 'masonry-gallery-few items-' . $itemCount : 'masonry-gallery';
        @endphp
        <div class="{{ $masonryClass }} my-4">
            @forelse ($contents as $content)
                <div class="masonry-item">
                    <a href="{{ route('content.detail', $content->id) }}" class="text-decoration-none">
                        <div class="content-clean-wrapper shadow-sm">
                            <img src="{{ asset('storage/' . $content->path_low_res) }}"
                                alt="{{ $content->content_title }}" class="img-fluid w-100 content-clean-image"
                                loading="lazy">
                        </div>
                    </a>
                </div>
            @empty
                <div class="w-100 text-center py-5" style="column-span: all;">
                    <p class="text-muted fs-5 mt-3">
                        There is no content available yet.
                    </p>
                </div>
            @endforelse
        </div>

        {{-- <div class="row my-4">
            @foreach ($contents as $content)
                <div class="col-md-4 mb-4">
                    <div class="card">
                        <a href="{{ route('content.detail', $content->id) }}" class="text-decoration-none">
                            <div class="content-clean-wrapper">
                                <img src="{{ asset('storage/' . $content->path_low_res) }}"
                                    alt="{{ $content->content_title }}"
                                    class="img-fluid w-100 rounded-3 shadow-sm content-clean-image">
                            </div>
                        </a>
                    </div>
                </div>
            @endforeach
        </div> --}}
        {{-- <div class="mt-4 d-flex justify-content-center">
            {{ $folders->links() }}
        </div> --}}
    </div>
@endsection
