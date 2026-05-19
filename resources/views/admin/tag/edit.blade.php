@extends('layouts.admin')

@section('content')
    <div class="container-fluid px-4 admin-page-wrapper">
        <div class="row justify-content-center">
            <div class="col-12 col-lg-8 col-xl-7">
                <div class="card admin-card">
                    <div class="card-body p-4 p-md-5">
                        <div class="mb-4 text-center">
                            <h2 class="card-title mb-1">
                                {{ __('Edit Tag') }}
                            </h2>
                            <p class="text-muted mb-0">
                                Update tag information.
                            </p>
                        </div>

                        <form method="POST" action="{{ route('admin.tag.update', $tag) }}" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')

                            <div class="mb-3">
                                <label for="tag_name" :value="__('Tag Name')" class="form-label">Tag Name</label>
                                <input id="tag_name" name="tag_name" type="text"
                                    value="{{ old('tag_name', $tag->tag_name) }}"
                                    class="form-control @error('tag_name') is-invalid @enderror">

                                @error('tag_name')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <button class="w-100 btn btn-lg btn-dark" type="submit">
                                Update Tag
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
