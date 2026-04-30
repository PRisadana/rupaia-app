@extends('layouts.admin')

@section('content')
    <div class="container-fluid px-4">
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="d-flex justify-content-between align-items-center my-4">
            <h1 class="m-0">Contents</h1>

            {{-- <a href="{{ route('admin.user.create') }}"
                class="btn btn-outline-secondary px-4 d-inline-flex align-items-center fw-semibold">
                {{ __('Add User') }}
            </a> --}}
        </div>
        <hr>
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th scope="col">No</th>
                    <th scope="col">Author</th>
                    {{-- <th scope="col">Folder</th> --}}
                    <th scope="col">Content Title</th>
                    <th scope="col">Content Description</th>
                    <th scope="col">Price (Rp)</th>
                    <th scope="col">Visibility</th>
                    <th scope="col">Status</th>
                    <th scope="col">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($contents as $content)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        {{-- <td>{{ $content->user->name }}</td>
                        <td>{{ $content->content_title }}</td> --}}
                        <td><a href="{{ route('authors.show', $content->user->id) }}"
                                class="text-dark fw-semibold">{{ $content->user->name }}</a></td>
                        <td><a
                                href="{{ route('content.detail', $content->id) }}"class="text-dark fw-semibold text-color-dark">{{ $content->content_title }}</a>
                        </td>
                        <td>{{ $content->content_description }}</td>
                        <td>{{ number_format($content->price, 0, ',', '.') }}</td>
                        <td>{{ $content->visibility }}</td>
                        <td>{{ $content->status }}</td>
                        <td>
                            <a href="{{ route('admin.content.status.edit', $content) }}"
                                class="btn btn-sm btn-outline-primary"><i class="fi fi-rr-edit"></i></a>
                            {{-- <form action="#" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger"
                                    onclick="return confirm('Are you sure you want to delete this content?')"><i
                                        class="fi fi-rr-trash"></i></button>
                            </form> --}}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center">No contents found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="my-2">
            {{ $contents->links() }}
        </div>
    </div>
@endsection

{{-- <style>
    /* --- Kustomisasi Paginasi Minimalis --- */
    .pagination {
        gap: 0.35rem; /* Memberikan jarak antar tombol agar tidak menempel */
    }

    .page-item:first-child .page-link,
    .page-item:last-child .page-link {
        border-radius: 50px; /* Membulatkan tombol panah Prev/Next */
    }

    .page-item .page-link {
        border-radius: 50px; /* Mengubah kotak menjadi bulat/pil */
        border: 1px solid transparent; /* Menghilangkan garis tepi bawaan */
        color: #495057; /* Warna teks abu-abu gelap */
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
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15); /* Bayangan halus */
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
