@extends('layouts.main')

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-10 ">
            <div class="card shadow-sm mb-4">
                <div class="card-body p-4 p-md-5">
                    <header>
                        <h2 class="card-title mb-3 text-center">
                            {{ __('Select Content for Showcase') }}
                        </h2>
                    </header>

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('showcase.from.content.store') }}" enctype="multipart/form-data">
                        @csrf

                        <div class="row g-3">
                            @forelse ($contents as $content)
                                <div class="col-6 col-md-4 col-lg-3">
                                    <label class="w-100 cursor-pointer selectable-card" for="content_{{ $content->id }}">

                                        <input type="radio" name="content_id" id="content_{{ $content->id }}"
                                            value="{{ $content->id }}" class="d-none content-selector" required>

                                        <div
                                            class="content-clean-wrapper position-relative rounded-3 overflow-hidden shadow-sm transition-all">
                                            <img src="{{ asset('storage/' . $content->path_low_res) }}"
                                                alt="{{ $content->content_title }}"
                                                class="img-fluid w-100 content-clean-image"
                                                style="object-fit: cover; aspect-ratio: 1/1;">

                                            <div
                                                class="selection-overlay position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center bg-primary bg-opacity-50">
                                                <i class="fi fi-rr-check-circle text-white" style="font-size: 3rem;"></i>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                            @empty
                                <div class="col-12">
                                    <p class="text-center text-muted fs-4 mt-5">
                                        There is no public content available yet.
                                    </p>
                                </div>
                            @endforelse
                            <div class="mt-4">
                                {{ $contents->links() }}
                            </div>
                        </div>

                        <button class="w-100 btn btn-lg btn-dark" type="submit">
                            upload
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

<style>
    .cursor-pointer {
        cursor: pointer;
    }

    .transition-all {
        transition: all 0.3s ease;
    }

    .selectable-card .selection-overlay {
        opacity: 0;
        visibility: hidden;
    }

    .content-selector:checked+.content-clean-wrapper .selection-overlay {
        opacity: 1;
        visibility: visible;
    }

    .content-selector:checked+.content-clean-wrapper {
        border: 4px solid #0d6efd;
        transform: scale(0.95);
    }
</style>
