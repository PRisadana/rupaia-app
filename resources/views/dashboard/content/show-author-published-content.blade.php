@extends('layouts.main')

@section('content')
    <div class="container my-4">
        <div class="p-5 text-center bg-body-tertiary rounded-3">
            <img src="{{ $user->profile_photo_path ? asset('storage/' . $user->profile_photo_path) : asset('aset/rupaia_logo.png') }}"
                alt="Profile" class="rounded-circle shadow-sm" width="100" height="100" style="object-fit: cover;">
            <h1 class="text-body-emphasis my-3">{{ $user->name }}</h1>
            <p class="col-lg-8 mx-auto my-3 fs-5 text-muted">
                {{ $user->bio ?? 'This user has not set a bio yet.' }}
            </p>
        </div>
    </div>

    <ul class="nav nav-underline justify-content-center">
        <li class="nav-item">
            <a class="nav-link" aria-current="page" href="{{ route('authors.show', $user->id) }}">
                {{ $user->name }}'s Portfolio
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link active" href="{{ route('authors.show.published', $user->id) }}">
                Published Content
            </a>
        </li>
    </ul>

    <div class="container pb-5 my-4">
        @php
            $itemCount = $contents->count();
            // Buat class tambahan jika itemnya sedikit agar letaknya bisa ke tengah
            $masonryClass = $itemCount < 4 ? 'masonry-gallery-few items-' . $itemCount : 'masonry-gallery';
        @endphp

        <div class="{{ $masonryClass }}">
            @forelse ($contents as $content)
                <div class="masonry-item">
                    <a href="{{ route('content.detail', $content->id) }}" class="text-decoration-none">
                        <div class="content-clean-wrapper shadow-sm">
                            <img src="{{ asset('storage/' . $content->path_low_res) }}" alt="{{ $content->content_title }}"
                                class="img-fluid w-100 content-clean-image" loading="lazy">
                        </div>
                    </a>
                </div>
            @empty
                <div class="w-100 text-center py-5" style="column-span: all;">
                    <p class="text-muted fs-4 mt-5">
                        There is no public content available yet.
                    </p>
                </div>
            @endforelse
        </div>
    </div>
@endsection

{{-- <style>
    /* --- CSS Masonry Gallery (Perbaikan) --- */
    .masonry-gallery,
    .masonry-gallery-few {
        column-gap: 1.25rem;
        column-fill: balance;
        /* Memaksa browser menyeimbangkan isi semua kolom */
        width: 100%;
    }

    /* Pengaturan default untuk HP (1 atau 2 kolom) */
    .masonry-gallery {
        column-count: 2;
    }

    .masonry-gallery-few {
        column-count: 2;
    }

    @media (min-width: 768px) {
        .masonry-gallery {
            column-count: 3;
        }

        .masonry-gallery-few.items-2 {
            column-count: 2;
        }

        .masonry-gallery-few.items-3 {
            column-count: 3;
        }
    }

    @media (min-width: 992px) {

        /* Jika item banyak, buat 4 kolom penuh */
        .masonry-gallery {
            column-count: 4;
        }

        /* Jika item sedikit, batas jumlah kolomnya agar tidak ada ruang bolong di kanan */
        .masonry-gallery-few.items-1 {
            column-count: 1;
            max-width: 400px;
            margin: 0 auto;
        }

        .masonry-gallery-few.items-2 {
            column-count: 2;
            max-width: 800px;
            margin: 0 auto;
        }

        .masonry-gallery-few.items-3 {
            column-count: 3;
            max-width: 1100px;
            margin: 0 auto;
        }
    }

    .masonry-item {
        break-inside: avoid;
        -webkit-column-break-inside: avoid;
        page-break-inside: avoid;
        margin-bottom: 1.25rem;
        display: block;
        /* Diubah dari inline-block menjadi block */
        width: 100%;
    }

    /* --- Efek Hover & Wrapper --- */
    .content-clean-wrapper {
        overflow: hidden;
        border-radius: 0.75rem;
        background: #f8f9fa;
        display: block;
    }

    .content-clean-image {
        display: block;
        width: 100%;
        height: auto;
        transition: transform 0.35s ease;
    }

    .content-clean-wrapper:hover .content-clean-image {
        transform: scale(1.05);
    }
</style> --}}
