@extends('layouts.main')

@section('content')
    <div class="container col-xxl-8 px-4 py-5 ">
        <div class="row flex-lg-row-reverse align-items-center justify-center g-5 py-5">
            <div class="col-10 col-sm-8 col-lg-6 items-center ">
                <img src=" {{ url('aset\welcome_image.jpg') }}  " class=" img-fluid d-block" alt="Bootstrap Themes"
                    width="700" loading="lazy">
            </div>
            <div class="col-lg-6 items-center">
                <h1 class="display-5 fw-bold text-body-emphasis lh-1 mb-3">
                    Discover authentic visual works from Indonesia's best creators.
                </h1>
                <p class="lead">
                    Explore a collection of premium photos, illustrations, and videos for your projects, or start selling
                    your work on a secure and trusted platform.
                </p>
                <form role="search">
                    <input class="form-control" type="search" placeholder="Search photos and more..." aria-label="Search ">
                </form>
            </div>
        </div>

        <div class="row">
            <div class="col-12 text-center mb-4">
                <h2>Explore the Latest Content</h2>
                <p class="text-muted">Check out the best work from our creators.</p>
            </div>
        </div>
    </div>

    <div class="row">
        @forelse ($contents as $content)
            <div class="col-md-4 mb-4">
                <div class="card shadow-sm h-100">

                    <a href="#">
                        <img src="{{ asset('storage/' . $content->path_low_res) }}" class="card-img-top"
                            alt="{{ $content->content_title }}" style="height: 220px; object-fit: cover;">
                    </a>

                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title text-truncate">{{ $content->content_title }}</h5>

                        <p class="card-text text-muted" style="font-size: 0.9rem;">
                            by: {{ $content->user->name }}
                        </p>

                        <p class="card-text mt-auto">
                            @foreach ($content->tags as $tag)
                                <span class="badge bg-secondary">{{ $tag->name_tag }}</span>
                            @endforeach
                        </p>
                    </div>

                </div>
            </div>
        @empty
            <div class="col-12">
                <p class="text-center text-muted fs-4 mt-5">There is no public content available yet.</p>
            </div>
        @endforelse
    </div>

    <div class="mt-4 d-flex justify-content-center">
        {{ $contents->links() }}
    </div>
@endsection
