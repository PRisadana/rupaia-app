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
            <h1 class="m-0">Showcase Items</h1>
        </div>
        <hr>
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th scope="col">No</th>
                    <th scope="col">Author</th>
                    <th scope="col">Is Content</th>
                    <th scope="col">Item Source</th>
                    <th scope="col">Description</th>
                    <th scope="col">Status</th>
                    <th scope="col">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($showcaseItems as $showcaseItem)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td><a href="{{ route('authors.show', $showcaseItem->user->id) }}"
                                class="text-dark fw-semibold">{{ $showcaseItem->user->name }}</a></td>
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
                        <td>{{ $showcaseItem->item_source }}</td>
                        <td><a href="{{ route('authors.show.detail', $showcaseItem->id) }}" class="text-dark fw-semibold">
                                {{ $showcaseItem->description }}
                            </a></td>
                        <td>{{ $showcaseItem->status }}</td>
                        <td>
                            <a href="{{ route('admin.showcase.status.edit', $showcaseItem) }}"
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
            {{ $showcaseItems->links() }}
        </div>
    </div>
@endsection
