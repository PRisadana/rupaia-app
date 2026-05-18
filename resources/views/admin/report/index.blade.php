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
            <h1 class="m-0">Published Content Reports</h1>
        </div>
        <hr>
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th scope="col">No</th>
                    <th scope="col">Content</th>
                    <th scope="col">Seller</th>
                    {{-- <th scope="col">Content From</th> --}}
                    <th scope="col">Report Status</th>
                    <th scope="col">Action Taken</th>
                    <th scope="col">Pending Reports</th>
                    <th scope="col">Total Reports</th>
                    <th scope="col">Latest Reason</th>
                    <th scope="col">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($reportedContents as $content)
                    @php
                        $latestReport = $content->reports->first();
                    @endphp

                    <tr>
                        <td>{{ $loop->iteration }}</td>

                        <td>
                            <a href="{{ route('content.detail', $content->id) }}" class="text-dark fw-semibold">
                                {{ $content->content_title }}
                            </a>
                        </td>

                        <td>
                            <a href="{{ route('authors.show', $content->user->id) }}" class="text-dark fw-semibold">
                                {{ $content->user->name }}
                            </a>
                        </td>

                        {{-- <td>
                            @if ($latestReport->content_id)
                                <span>Published Content</span>
                            @elseif ($latestReport->showcase_id)
                                <span>Showcase Content</span>
                            @else
                                <span class="text-muted"> Null </span>
                            @endif
                        </td> --}}

                        <td>
                            @if ($latestReport)
                                @if ($latestReport->status === 'pending')
                                    <span class="badge bg-warning text-dark">Pending</span>
                                @elseif ($latestReport->status === 'ignored')
                                    <span class="badge bg-secondary">Ignored</span>
                                @elseif ($latestReport->status === 'resolved')
                                    <span class="badge bg-success">Resolved</span>
                                @else
                                    <span class="badge bg-light text-dark border">
                                        {{ ucfirst($latestReport->status) }}
                                    </span>
                                @endif
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>

                        <td>
                            @if ($latestReport?->action_taken === 'takedown')
                                <span class="badge bg-danger">Takedown</span>
                            @elseif ($latestReport?->action_taken === 'ignored')
                                <span class="badge bg-secondary">Ignored</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>

                        <td>
                            <span class="badge bg-warning text-dark">
                                {{ $content->pending_reports_count ?? 0 }}
                            </span>
                        </td>

                        <td>
                            <span class="badge bg-secondary">
                                {{ $content->total_reports_count ?? 0 }}
                            </span>
                        </td>

                        <td>
                            @if ($latestReport)
                                <span class="badge bg-light text-dark border">
                                    {{ $latestReport->reason_label ?? ucfirst($latestReport->reason) }}
                                </span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>

                        <td>
                            <a href="{{ route('admin.report.show', $content->id) }}"
                                class="btn btn-sm btn-outline-secondary">
                                <i class="fi fi-rr-eye"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted">
                            No reported contents found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="my-2">
            {{ $reportedContents->links() }}
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
