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
                <h1 class="m-0">Folders</h1>
                <p class="text-muted mb-0">Manage and review folder status.</p>
            </div>
        </div>

        <div class="admin-table-wrapper">
            <div class="table-responsive">
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
                                        <a href="{{ route('folder.show', $folder->parent->id) }}"
                                            class="text-dark fw-semibold">
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
                                <td>
                                    @if ($folder->visibility === 'public')
                                        <span class="badge bg-success">public</span>
                                    @elseif ($folder->visibility === 'private')
                                        <span class="badge bg-secondary">private</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($folder->is_bundle)
                                        <span class="badge bg-success">Yes</span>
                                    @else
                                        <span class="badge bg-secondary">No</span>
                                    @endif
                                </td>
                                <td>{{ number_format($folder->bundle_price, 0, ',', '.') }}</td>
                                <td>
                                    @if ($folder->status === 'active')
                                        <span class="badge bg-success">Active</span>
                                    @elseif ($folder->status === 'banned')
                                        <span class="badge bg-danger">Banned</span>
                                    @else
                                        <span class="badge bg-secondary">{{ ucfirst($folder->status) }}</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('admin.folder.status.edit', $folder) }}"
                                        class="btn btn-sm btn-outline-primary"><i class="fi fi-rr-edit"></i></a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center">No contents found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $folders->links() }}
            </div>
        </div>
    </div>
@endsection
