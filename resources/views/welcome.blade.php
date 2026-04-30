@extends('layouts.main')

@section('content')
    <div class="container col-xxl-8 px-4 py-5 ">
        <div class="row flex-lg-row-reverse align-items-center justify-center g-5 py-5">
            <div class="col-10 col-sm-8 col-lg-6 items-center ">
                <img src=" {{ url('aset\welcome_image.jpg') }}  " class=" img-fluid d-block" alt="Bootstrap Themes"
                    width="700" loading="lazy">
            </div>
            <div class="col-lg-6 items-center">
                <h1 class="display-5 fw-bold text-body-emphasis lh-1 mb-3">
                    Discover authentic visual works from Indonesia's best creators.
                </h1>
                <p class="lead">
                    Explore a collection of premium photos, illustrations, and videos for your projects, or start selling
                    your work on a secure and trusted platform.
                </p>
                <form role="search">
                    <input class="form-control" type="search" placeholder="Search photos and more..." aria-label="Search ">
                </form>
            </div>
        </div>

        <div class="row">
            <div class="col-12 text-center mb-4">
                <h2>Explore the Latest Content</h2>
                <p class="text-muted">Check out the best work from our creators.</p>
            </div>
        </div>
    </div>

    <div class="container pb-5">
        <div class="masonry-gallery">
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

    <div class="container d-flex justify-content-center mt-2 mb-5">
        {{ $contents->links() }}
    </div>
@endsection

{{-- <style>
    /* Mengatur jumlah kolom berdasarkan ukuran layar (Responsif) */
    .masonry-gallery {
        column-count: 2;
        /* Default untuk HP */
        column-gap: 1rem;
        /* Jarak antar kolom horizontal */
    }

    @media (min-width: 768px) {
        .masonry-gallery {
            column-count: 3;
            /* Untuk Tablet */
        }
    }

    @media (min-width: 992px) {
        .masonry-gallery {
            column-count: 4;
            /* Untuk Layar Laptop/Desktop */
            column-gap: 1.25rem;
        }
    }

    /* Memastikan gambar tidak terpotong ke kolom sebelahnya */
    .masonry-item {
        break-inside: avoid;
        -webkit-column-break-inside: avoid;
        page-break-inside: avoid;
        margin-bottom: 1.25rem;
        /* Jarak vertikal antar gambar */
        display: inline-block;
        width: 100%;
    }

    /* Efek visual agar terlihat lebih elegan */
    .content-clean-wrapper {
        overflow: hidden;
        border-radius: 0.75rem;
        /* Radius yang sedikit lebih modern */
        background: #f8f9fa;
        /* Background saat gambar belum termuat */
    }

    .content-clean-image {
        display: block;
        transition: transform 0.35s ease;
        /* Transisi diperhalus */
    }

    .content-clean-wrapper:hover .content-clean-image {
        transform: scale(1.05);
        /* Sedikit diperbesar saat hover */
    }

    /* --- Kustomisasi Paginasi Minimalis --- */
    .pagination {
        gap: 0.35rem;
        /* Memberikan jarak antar tombol agar tidak menempel */
    }

    .page-item:first-child .page-link,
    .page-item:last-child .page-link {
        border-radius: 50px;
        /* Membulatkan tombol panah Prev/Next */
    }

    .page-item .page-link {
        border-radius: 50px;
        /* Mengubah kotak menjadi bulat/pil */
        border: 1px solid transparent;
        /* Menghilangkan garis tepi bawaan */
        color: #495057;
        /* Warna teks abu-abu gelap */
        padding: 0.5rem 1rem;
        font-weight: 500;
        background-color: transparent;
        transition: all 0.3s ease;
    }

    /* Efek saat cursor diarahkan ke angka (Hover) */
    .page-item:not(.active):not(.disabled) .page-link:hover {
        background-color: #f8f9fa;
        color: #000;
        border-color: #e9ecef;
    }

    /* Efek halaman yang sedang aktif (Hitam Elegan) */
    .page-item.active .page-link {
        background-color: #212529;
        border-color: #212529;
        color: #fff;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
        /* Bayangan halus */
    }

    /* Efek saat tombol tidak bisa diklik (misal di halaman pertama/terakhir) */
    .page-item.disabled .page-link {
        color: #ced4da;
        background-color: transparent;
    }

    /* Menghilangkan efek outline biru saat diklik */
    .page-link:focus {
        box-shadow: none;
    }
</style> --}}
