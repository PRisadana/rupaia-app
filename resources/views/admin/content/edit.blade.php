@extends('layouts.admin')

@section('content')
    <div class="row justify-content-center my-4">
        <div class="col-lg-10 ">
            <div class="card shadow-sm mb-4">
                <div class="card-body p-4 p-md-5">
                    <header>
                        <h2 class="card-title mb-3 text-center">
                            {{ __('Edit Content') }}
                        </h2>
                    </header>

                    <form method="POST" action="{{ route('admin.content.status.update', $content) }}"
                        enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label class="form-label">
                                <h4>Author</h4>
                            </label>
                            <div class="form-control-plaintext">
                                {{ $content->user->name }}
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">
                                <h4>Content Title</h4>
                            </label>
                            <div class="form-control-plaintext">
                                {{ $content->content_title }}
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="status" :value="__('Status')" class="form-label">Status</label>
                            <select name="status" id="status" class="form-select @error('status') is-invalid @enderror">
                                <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>active
                                </option>
                                <option value="banned" {{ old('status') == 'banned' ? 'selected' : '' }}>
                                    banned
                                </option>
                                {{-- <option value="deleted" {{ old('status') == 'deleted' ? 'selected' : '' }}>
                                    deleted
                                </option> --}}
                            </select>

                            @error('status')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <button class="w-100 btn btn-lg btn-dark" type="submit">
                            Update Preset
                        </button>
                    </form>
                </div>
            </div>
        </div>
    @endsection
