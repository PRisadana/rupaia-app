@extends('layouts.admin')

@section('content')
    <div class="container-fluid px-4 admin-page-wrapper">
        <div class="d-flex justify-content-between align-items-center my-4 admin-page-title">
            <div>
                <h1 class="m-0">Users</h1>
                <p class="text-muted mb-0">Manage and review user status.</p>
            </div>
            <a href="{{ route('admin.user.create') }}"
                class="btn btn-outline-secondary px-4 py-2 d-inline-flex align-items-center align-items-center gap-2"><i
                    class="fi fi-rr-square-plus mt-1"></i>
                <span>Add User</span>
            </a>
        </div>

        <div class="admin-table-wrapper">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th scope="col">No</th>
                            <th scope="col">Name</th>
                            <th scope="col">Email</th>
                            <th scope="col">Role</th>
                            <th scope="col">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($users as $user)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $user->name }}</td>
                                <td>{{ $user->email }}</td>
                                <td>
                                    @if ($user->role === 'admin')
                                        <span class="badge bg-warning text-black">admin</span>
                                    @elseif ($user->role === 'seller')
                                        <span class="badge bg-success">seller</span>
                                    @elseif ($user->role === 'buyer')
                                        <span class="badge bg-secondary">buyer</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('admin.user.edit', $user) }}"
                                        class="btn btn-sm btn-outline-primary"><i class="fi fi-rr-edit"></i></a>
                                    <form action="{{ route('admin.user.destroy', $user) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger"
                                            onclick="return confirm('Are you sure you want to delete this user?')"><i
                                                class="fi fi-rr-trash"></i></button></button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center">No users found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $users->links() }}
            </div>
        </div>
    </div>
@endsection
