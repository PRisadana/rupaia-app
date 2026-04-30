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
            <h1 class="m-0">Tags</h1>

            <a href="{{ route('admin.tag.create') }}"
                class="btn btn-outline-secondary px-4 py-2 d-inline-flex align-items-center gap-2"><i
                    class="fi fi-rr-square-plus mt-1"></i>
                <span>Add Tag</span>
            </a>
        </div>
        <hr>
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th scope="col">No</th>
                    <th scope="col">Tag Name</th>
                    <th scope="col">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($tags as $tag)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $tag->tag_name }}</td>
                        <td>
                            <a href="{{ route('admin.tag.edit', $tag) }}" class="btn btn-sm btn-outline-primary"><i
                                    class="fi fi-rr-edit"></i></a>
                            <form action="{{ route('admin.tag.destroy', $tag) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger"
                                    onclick="return confirm('Are you sure you want to delete this tag?')"><i
                                        class="fi fi-rr-trash"></i></button></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center">No tags found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="my-2">
            {{ $tags->links() }}
        </div>
    </div>
@endsection
