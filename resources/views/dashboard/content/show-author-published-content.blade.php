@extends('layouts.main')

@section('content')
    <div class="container my-4">
        <div class="p-5 text-center bg-body-tertiary rounded-3">
            <img src="{{ $user->profile_photo_path ? asset('storage/' . $user->profile_photo_path) : asset('aset/rupaia_logo.png') }}"
                alt="Profile" class="rounded-circle" width="100" height="100" style="object-fit: cover;">
            <h1 class="text-body-emphasis my-3">{{ $user->name }}</h1>
            <p class="col-lg-8 mx-auto my-3 fs-5 text-muted">
                {{ $user->bio ?? 'This user has not set a bio yet.' }}
            </p>
        </div>
    </div>

    <ul class="nav nav-underline justify-content-center">
        <li class="nav-item">
            <a class="nav-link" aria-current="page" href="{{ route('authors.show', $user->id) }}"> {{ $user->name }}'s
                Portfolio</a>
        </li>
        <li class="nav-item">
            <a class="nav-link active" href="{{ route('authors.show.published', $user->id) }}">Published Content</a>
        </li>
    </ul>

    <div class="container pb-5 my-4">
        <div class="row g-3">
            @forelse ($contents as $content)
                <div class="col-6 col-md-4 col-lg-3">
                    <a href="{{ route('content.detail', $content->id) }}" class="text-decoration-none">
                        <div class="content-clean-wrapper">
                            <img src="{{ asset('storage/' . $content->path_low_res) }}" alt="{{ $content->content_title }}"
                                class="img-fluid w-100 rounded-3 shadow-sm content-clean-image">
                        </div>
                    </a>
                </div>
            @empty
                <div class="col-12">
                    <p class="text-center text-muted fs-4 mt-5">
                        There is no public content available yet.
                    </p>
                </div>
            @endforelse
        </div>
    </div>
    </div>
@endsection

<style>
    .content-clean-wrapper {
        overflow: hidden;
        border-radius: 1rem;
        background: #fff;
    }

    .content-clean-image {
        display: block;
        transition: transform 0.25s ease-in-out;
    }

    .content-clean-wrapper:hover .content-clean-image {
        transform: scale(1.02);
    }
</style>
