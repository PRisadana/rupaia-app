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
            <h1 class="m-0">Folders</h1>

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
                    <th scope="col">Parent</th>
                    <th scope="col">Folder Name</th>
                    <th scope="col">Folder Description</th>
                    <th scope="col">Visibility</th>
                    <th scope="col">Is Bundle</th>
                    <th scope="col">Bundle Price (Rp)</th>
                    <th scope="col">Status</th>
                    <th scope="col">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($folders as $folder)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td><a href="{{ route('authors.show', $folder->user->id) }}"
                                class="text-dark fw-semibold">{{ $folder->user->name }}</a></td>
                        <td>
                            @if ($folder->parent_id)
                                <a href="{{ route('folder.show', $folder->parent->id) }}" class="text-dark fw-semibold">
                                    {{ $folder->parent->folder_name }}
                                </a>
                            @else
                                <span class="text-muted fst-italic">Null</span>
                            @endif
                        </td>
                        <td><a
                                href="{{ route('folder.show', $folder->id) }}"class="text-dark fw-semibold text-color-dark">{{ $folder->folder_name }}</a>
                        </td>
                        <td>{{ $folder->folder_description }}</td>
                        <td>{{ $folder->visibility }}</td>
                        <td>{{ $folder->is_bundle ? 'Yes' : 'No' }}</td>
                        <td>{{ number_format($folder->bundle_price, 0, ',', '.') }}</td>
                        <td>{{ $folder->status }}</td>
                        <td>
                            <a href="{{ route('admin.folder.status.edit', $folder) }}"
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
            {{ $folders->links() }}
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
