@extends('layouts.admin')

@section('content')
    <div class="container-fluid px-4 admin-page-wrapper">
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="d-flex justify-content-between align-items-center my-4 admin-page-title">
            <div>
                <h1 class="m-0">Showcase Items</h1>
                <p class="text-muted mb-0">Manage and review showcase item status.</p>
            </div>
        </div>

        <div class="admin-table-wrapper">
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead>
                        <tr>
                            <th scope="col" width="70">No</th>
                            <th scope="col">Author</th>
                            <th scope="col">Is Content</th>
                            <th scope="col">Item Source</th>
                            <th scope="col">Description</th>
                            <th scope="col">Status</th>
                            <th scope="col" width="100">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($showcaseItems as $showcaseItem)
                            <tr>
                                <td>{{ $loop->iteration + ($showcaseItems->currentPage() - 1) * $showcaseItems->perPage() }}
                                </td>

                                <td>
                                    <a href="{{ route('authors.show', $showcaseItem->user->id) }}"
                                        class="text-dark fw-semibold">
                                        {{ $showcaseItem->user->name }}
                                    </a>
                                </td>

                                <td>
                                    @if ($showcaseItem->content)
                                        <a href="{{ route('content.detail', $showcaseItem->content->id) }}"
                                            class="text-dark fw-semibold">
                                            {{ $showcaseItem->content->content_title }}
                                        </a>
                                    @else
                                        <span class="text-muted fst-italic">Null</span>
                                    @endif
                                </td>

                                <td>
                                    @if ($showcaseItem->item_source === 'custom')
                                        <span class="badge bg-warning text-black">custom</span>
                                    @elseif ($showcaseItem->item_source === 'content')
                                        <span class="badge bg-secondary">content</span>
                                    @endif
                                </td>

                                <td>
                                    <a href="{{ route('authors.show.detail', $showcaseItem->id) }}"
                                        class="text-dark fw-semibold">
                                        {{ Str::limit($showcaseItem->description, 60) }}
                                    </a>
                                </td>

                                <td>
                                    @if ($showcaseItem->status === 'active')
                                        <span class="badge bg-success">Active</span>
                                    @elseif ($showcaseItem->status === 'banned')
                                        <span class="badge bg-danger">Banned</span>
                                    @else
                                        <span class="badge bg-secondary">{{ ucfirst($showcaseItem->status) }}</span>
                                    @endif
                                </td>

                                <td>
                                    <a href="{{ route('admin.showcase.status.edit', $showcaseItem) }}"
                                        class="btn btn-sm btn-outline-primary">
                                        <i class="fi fi-rr-edit"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    No showcase items found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $showcaseItems->links() }}
            </div>
        </div>
    </div>
@endsection
