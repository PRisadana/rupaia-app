@extends('layouts.admin')

@section('content')
    <div class="container-fluid px-4 admin-page-wrapper">
        <div class="d-flex justify-content-between align-items-center my-4 admin-page-title">
            <div>
                <h1 class="m-0">Licenses</h1>
                <p class="text-muted mb-0">Manage and review licenses</p>
            </div>
            <a href="{{ route('admin.license.create') }}"
                class="btn btn-outline-secondary px-4 py-2 d-inline-flex align-items-center gap-2"><i
                    class="fi fi-rr-square-plus mt-1"></i>
                <span>Add License</span>
            </a>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>Action failed:</strong>
                <ul class="mb-0 mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="admin-table-wrapper">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th scope="col">No</th>
                            <th scope="col">Licenses Name</th>
                            <th scope="col">Code</th>
                            <th scope="col">Description</th>
                            <th scope="col">Terms</th>
                            <th scope="col">Is Active</th>
                            <th scope="col">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($licenses as $license)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $license->name }}</td>
                                <td><span class="badge bg-info">{{ $license->code }}</span></td>
                                <td>{{ $license->description }}</td>
                                <td>{{ $license->terms }}</td>
                                <td>
                                    @if ($license->is_active)
                                        <span class="badge bg-success">Yes</span>
                                    @else
                                        <span class="badge bg-danger">No</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('admin.license.edit', $license) }}"
                                        class="btn btn-sm btn-outline-primary"><i class="fi fi-rr-edit"></i></a>
                                    <form action="{{ route('admin.license.destroy', $license) }}" method="POST"
                                        class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger"
                                            onclick="return confirm('Are you sure you want to delete this license?')"><i
                                                class="fi fi-rr-trash"></i></button></button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">No licenses found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $licenses->links() }}
            </div>
        </div>
    </div>
@endsection
