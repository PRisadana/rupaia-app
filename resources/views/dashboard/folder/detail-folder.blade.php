@extends('layouts.main')

@section('content')
    <div class="container my-4">
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        <h1>Detail Folder {{ $currentFolder->folder_name }}</h1>
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('folder.index') }}">Folder</a>
                </li>
                @foreach ($breadcrumbs as $crumb)
                    <li class="breadcrumb-item {{ $loop->last ? 'active' : '' }}">
                        @if ($loop->last)
                            {{ $crumb->folder_name }}
                        @else
                            <a href="{{ route('detail.folder.show', $crumb) }}">{{ $crumb->folder_name }}</a>
                        @endif
                    </li>
                @endforeach
            </ol>
        </nav>

        <div class="d-inline-flex gap-2 my-2">
            <button class="d-inline-flex align-items-center btn btn-primary btn-sm px-4" type="button">
                @if ($currentFolder)
                    <a href="{{ route('detail.folder.create', ['id_parent' => $currentFolder->id]) }}"
                        class="text-white nav-link">
                        Add Subfolder
                    </a>
                @else
                    <a href="{{ route('folder.create') }}" class="btn btn-outline-secondary btn-sm">
                        Add Folder di Root
                    </a>
                @endif
                {{-- <a class="text-white nav-link"
                    href="{{ route('detail.folder.create', $folders) }}">{{ __('Add Folder') }}</a> --}}
            </button>
            <button class="d-inline-flex align-items-center btn btn-outline-secondary btn-sm px-4" type="button">
                @if ($currentFolder)
                    <a href="{{ route('content.detail.folder.create', ['id_parent' => $currentFolder->id]) }}"
                        class="text-dark nav-link">
                        Add Content
                    </a>
                @else
                    <a href="{{ route('folder.create') }}" class="btn btn-outline-secondary btn-sm">
                        Add Folder di Root
                    </a>
                @endif
            </button>
        </div>

        <div class="row my-4">
            @foreach ($folders as $folder)
                <div class="col-md-4 mb-4">
                    <div class="card">
                        {{-- <img src="{{ asset('storage/' . $content->path_low_res) }}" class="card-img-top"
                        alt="{{ $content->content_title }}"> --}}

                        <div class="card-body">
                            <h5><a href="{{ route('detail.folder.show', $folder) }}"
                                    class="card-title">{{ $folder->folder_name }}</a>
                            </h5>
                            <p class="card-text">Description: {{ $folder->folder_description }}</p>
                            {{-- <p class="card-text">Visibility Content: {{ $content->visibility_content }}</p> --}}
                            <div class="card">
                                <div></div>
                            </div>
                            <div class="d-flex flex-row mb-2 my-3">
                                <a href="{{ route('folder.edit', $folder) }}"
                                    class="btn btn-sm btn-outline-primary mx-1">Edit</a>

                                <form action="{{ route('folder.destroy', $folder) }} " method="POST"
                                    onsubmit="return confirm ('Are you sure for delete this folder?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="row my-4">
            @foreach ($contents as $content)
                <div class="col-md-4 mb-4">
                    <div class="card">
                        <img src="{{ asset('storage/' . $content->path_low_res) }}" class="card-img-top"
                            alt="{{ $content->content_title }}">

                        <div class="card-body">
                            <h5 class="card-title">{{ $content->content_title }}</h5>
                            <p class="card-text">Description: {{ $content->content_description }}</p>
                            <p class="card-text">Folder: {{ $content->folder->folder_name }}</p>
                            <p class="card-text">Visibility Content: {{ $content->visibility_content }}</p>
                            <p class="card-text">
                                Tags:
                                @foreach ($content->tags as $tag)
                                    <span class="badge bg-secondary">{{ $tag->name_tag }}</span>
                                @endforeach
                            </p>
                            <div col-4>

                            </div>
                            <div class="card">
                                <div></div>
                            </div>
                            <div class="d-flex flex-row mb-2 my-3">
                                <a href="{{ route('content.edit', $content) }}"
                                    class="btn btn-sm btn-outline-primary mx-1">Edit</a>

                                <form action="{{ route('content.destroy', $content) }}" method="POST"
                                    onsubmit="return confirm ('Are you sure for delete this content?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        {{-- <div class="mt-4 d-flex justify-content-center">
            {{ $folders->links() }}
        </div> --}}
    @endsection
