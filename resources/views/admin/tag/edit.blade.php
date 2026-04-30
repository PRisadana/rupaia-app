@extends('layouts.admin')

@section('content')
    <div class="row justify-content-center my-4">
        <div class="col-lg-10 ">
            <div class="card shadow-sm mb-4">
                <div class="card-body p-4 p-md-5">
                    <header>
                        <h2 class="card-title mb-3 text-center">
                            {{ __('Edit Tag') }}
                        </h2>
                    </header>

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
@endsection
