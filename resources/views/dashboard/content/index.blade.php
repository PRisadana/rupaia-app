@extends('layouts.main')

@section('content')
    <div class="container my-4">
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        <div class="p-5 text-center bg-body-tertiary rounded-3">
            <img src="{{ Auth::user()->profile_photo_path ? asset('storage/' . Auth::user()->profile_photo_path) : asset('aset/rupaia_logo.png') }}"
                alt="Profile" class="rounded-circle" width="100" height="100" style="object-fit: cover;">
            <h1 class="text-body-emphasis my-3">{{ Auth::user()->name }}</h1>
            <p class="col-lg-8 mx-auto my-3 fs-5 text-muted">
                {{ Auth::user()->bio ?? 'This user has not set a bio yet.' }}
            </p>
            <div class="d-inline-flex gap-2 my-4">
                <button class="d-inline-flex align-items-center btn btn-primary px-4 rounded-pill" type="button">
                    <a class="text-white nav-link" href="{{ route('profile.edit') }}">{{ __('Profile Setting') }}</a>
                </button>
                <button class="d-inline-flex align-items-center btn btn-outline-secondary px-4 rounded-pill" type="button">
                    <a class="text-dark nav-link" href="{{ route('content.create') }}">{{ __('Upload Content') }}</a>
                </button>
            </div>
        </div>
    </div>

    <ul class="nav nav-underline justify-content-center">
        <li class="nav-item">
            <a class="nav-link active" aria-current="page" href="{{ route('content.index') }}">Contents</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{ route('folder.index') }}">Folders</a>
        </li>
    </ul>

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

                            <button type="button" class="btn btn-sm btn-outline-secondary mx-1" data-bs-toggle="modal"
                                data-bs-target="#moveContentModal-{{ $content->id }}">
                                Move
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="moveContentModal-{{ $content->id }}" tabindex="-1"
                aria-labelledby="moveContentModalLabel-{{ $content->id }}" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <form method="POST" action="{{ route('content.move', $content) }}">
                            @csrf
                            @method('PUT')

                            <div class="modal-header">
                                <h5 class="modal-title" id="moveContentModalLabel-{{ $content->id }}">
                                    Move: {{ $content->content_title }}
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>

                            <div class="modal-body">
                                <div class="mb-3">
                                    <label class="form-label">Now Location</label>
                                    <div class="small text-muted">
                                        @if ($content->folder)
                                            @php
                                                $bc = collect();
                                                $temp = $content->folder;
                                                while ($temp) {
                                                    $bc->prepend($temp);
                                                    $temp = $temp->parent;
                                                }
                                            @endphp

                                            @foreach ($bc as $crumb)
                                                {{ $loop->first ? '' : ' / ' }}
                                                {{ $crumb->folder_name }}
                                            @endforeach
                                        @endif
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="move-folder-{{ $content->id }}" class="form-label">Move to folder</label>

                                    <select name="id_folder" id="move-folder-{{ $content->id }}" class="form-select">
                                        {{-- <option value="">Root (tanpa folder)</option> --}}

                                        @php
                                            $allFoldersForMove = auth()
                                                ->user()
                                                ->folders()
                                                ->orderBy('folder_name')
                                                ->get();

                                            $printTree = function ($folders, $parentId = null, $prefix = '') use (
                                                &$printTree,
                                                $content,
                                            ) {
                                                $children = $folders->where('id_parent', $parentId);
                                                foreach ($children as $f) {
                                                    // Jangan tampilkan folder tempat konten berada sekarang sebagai selected? boleh, tapi biasanya tetap boleh dipilih
                                                    echo '<option value="' .
                                                        $f->id .
                                                        '">' .
                                                        $prefix .
                                                        $f->folder_name .
                                                        '</option>';
                                                    $printTree($folders, $f->id, $prefix . '+ ');
                                                }
                                            };

                                            $printTree($allFoldersForMove, null, '');
                                        @endphp

                                    </select>
                                    <div class="form-text">
                                        Choose folder destination.
                                    </div>
                                </div>

                            </div>

                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary">Move</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endforeach
        <div class="my-2">
            {{ $contents->links() }}
        </div>
    </div>
@endsection
