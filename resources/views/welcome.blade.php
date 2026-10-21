@extends('layouts.main')

@section('content')
    @php
        $filterKeys = ['type', 'tag', 'license', 'seller', 'sale_type', 'min_price', 'max_price'];
        $hasPanelFilter = collect($filterKeys)->contains(fn($key) => request()->filled($key));
        $hasSearch = $hasPanelFilter || request()->filled('q');
    @endphp

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
                    Explore a collection of premium photos and illustrations for your projects, or start selling
                    your work on a secure and trusted platform.
                </p>

                <form id="catalogForm" role="search" method="GET" action="{{ route('dashboard') }}">
                    <div class="input-group">
                        <input class="form-control" type="search" name="q" value="{{ request('q') }}"
                            maxlength="100" placeholder="Search photos..." aria-label="Search">
                        <button class="btn btn-dark" type="submit">Search</button>
                        <button class="btn btn-outline-dark" type="button" data-bs-toggle="collapse"
                            data-bs-target="#filterPanel" aria-expanded="{{ $hasPanelFilter ? 'true' : 'false' }}">
                            Filters
                        </button>
                        <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">Reset</a>
                    </div>
                </form>

            </div>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="collapse {{ $hasPanelFilter ? 'show' : '' }}" id="filterPanel">
            <div class="card card-body mb-4">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label for="filter_type" class="form-label">Product type</label>
                        <select id="filter_type" name="type" form="catalogForm" class="form-select">
                            <option value="content" @selected($type === 'content')>Content</option>
                            <option value="bundle" @selected($type === 'bundle')>Bundle</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label for="filter_tag" class="form-label">Tag</label>
                        <select id="filter_tag" name="tag" form="catalogForm" class="form-select">
                            <option value="">All tags</option>
                            @foreach ($tags as $tag)
                                <option value="{{ $tag->id }}" @selected((string) request('tag') === (string) $tag->id)>
                                    {{ $tag->tag_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label for="filter_license" class="form-label">License</label>
                        <select id="filter_license" name="license" form="catalogForm" class="form-select">
                            <option value="">All licenses</option>
                            @foreach ($licenses as $license)
                                <option value="{{ $license->id }}" @selected((string) request('license') === (string) $license->id)>
                                    {{ $license->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label for="filter_seller" class="form-label">Seller</label>
                        <select id="filter_seller" name="seller" form="catalogForm" class="form-select">
                            <option value="">All sellers</option>
                            @foreach ($sellers as $seller)
                                <option value="{{ $seller->id }}" @selected((string) request('seller') === (string) $seller->id)>
                                    {{ $seller->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label for="filter_sale_type" class="form-label">Sale mode</label>
                        <select id="filter_sale_type" name="sale_type" form="catalogForm" class="form-select">
                            <option value="">All modes</option>
                            <option value="single_sale" @selected(request('sale_type') === 'single_sale')>Single Sale</option>
                            <option value="multi_sale" @selected(request('sale_type') === 'multi_sale')>Multi Sale</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label for="filter_min_price" class="form-label">Minimum price (Rp)</label>
                        <input id="filter_min_price" name="min_price" form="catalogForm" type="number" min="0"
                            step="1" class="form-control" value="{{ request('min_price') }}">
                    </div>

                    <div class="col-md-3">
                        <label for="filter_max_price" class="form-label">Maximum price (Rp)</label>
                        <input id="filter_max_price" name="max_price" form="catalogForm" type="number" min="0"
                            step="1" class="form-control" value="{{ request('max_price') }}">
                    </div>

                    <div class="col-md-3 d-flex align-items-end gap-2">
                        <button type="submit" form="catalogForm" class="btn btn-dark">Apply</button>
                    </div>
                </div>
                <div class="form-text mt-2">Tag and sale mode apply to content only.</div>
            </div>
        </div>

        <div class="row">
            <div class="col-12 text-center mb-4">
                @if ($hasSearch)
                    <h2>Search Results</h2>
                    <p class="text-muted">
                        {{ ($contents ?? $bundles)->total() }} result(s) found.
                    </p>
                @else
                    <h2>Explore the Latest Content</h2>
                    <p class="text-muted">Check out the best work from our creators.</p>
                @endif
            </div>
        </div>
    </div>

    <div class="container pb-5">
        <div class="masonry-gallery">
            @if ($type === 'bundle')
                @forelse ($bundles as $bundle)
                    @php $cover = $bundle->contents->first(); @endphp
                    <div class="masonry-item">
                        <a href="{{ route('folder.show', $bundle->id) }}" class="text-decoration-none">
                            <div class=" position-relative content-clean-wrapper shadow-sm overflow-hidden">
                                @if ($cover)
                                    <img src="{{ asset('storage/' . $cover->path_low_res) }}"
                                        alt="{{ $bundle->folder_name }}" class="img-fluid w-100 content-clean-image"
                                        loading="lazy">
                                @else
                                    <div class="p-5 text-center text-muted">No preview</div>
                                @endif
                                <span class="badge bg-dark position-absolute top-0 end-0 m-2"
                                    style="z-index: 10;">Bundle</span>
                                <span class="badge bg-light text-dark position-absolute bottom-0 start-0 m-2"
                                    style="z-index: 10;">
                                    {{ $bundle->folder_name }} · Rp
                                    {{ number_format($bundle->bundle_price, 0, ',', '.') }}
                                </span>
                            </div>
                        </a>
                    </div>
                @empty
                    <div class="w-100 text-center py-5" style="column-span: all;">
                        <p class="text-muted fs-4 mt-5">
                            {{ $hasSearch ? 'No bundles match your search.' : 'There is no public bundle available yet.' }}
                        </p>
                    </div>
                @endforelse
            @else
                @forelse ($contents as $content)
                    <div class="masonry-item">
                        <a href="{{ route('content.detail', $content->id) }}" class="text-decoration-none">
                            <div class=" position-relative content-clean-wrapper shadow-sm overflow-hidden">
                                <img src="{{ asset('storage/' . $content->path_low_res) }}"
                                    alt="{{ $content->content_title }}" class="img-fluid w-100 content-clean-image"
                                    loading="lazy">
                                @if ($content->sale_type === 'single_sale' && $content->sale_status === 'available')
                                    <span class="badge bg-warning text-dark position-absolute top-0 end-0 m-2"
                                        style="z-index: 10;">
                                        Premium
                                    </span>
                                @endif
                                <span class="badge bg-light text-dark position-absolute bottom-0 start-0 m-2"
                                    style="z-index: 10;">
                                    Rp {{ number_format($content->price, 0, ',', '.') }}
                                </span>
                            </div>
                        </a>
                    </div>
                @empty
                    <div class="w-100 text-center py-5" style="column-span: all;">
                        <p class="text-muted fs-4 mt-5">
                            {{ $hasSearch ? 'No content matches your search.' : 'There is no public content available yet.' }}
                        </p>
                    </div>
                @endforelse
            @endif
        </div>
    </div>

    <div class="my-2">
        {{ ($contents ?? $bundles)->links() }}
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const typeSelect = document.getElementById('filter_type');
            const tagSelect = document.getElementById('filter_tag');
            const saleTypeSelect = document.getElementById('filter_sale_type');

            // Tag and sale mode apply to content only, so disable them for bundles.
            function toggleContentOnlyFilters() {
                const isBundle = typeSelect.value === 'bundle';
                tagSelect.disabled = isBundle;
                saleTypeSelect.disabled = isBundle;
            }

            typeSelect.addEventListener('change', toggleContentOnlyFilters);
            toggleContentOnlyFilters();
        });
    </script>
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
