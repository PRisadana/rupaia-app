@extends('layouts.main')

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-10 ">
            <div class="card shadow-sm mb-4">
                <div class="card-body p-4 p-md-5">
                    <header>
                        <h2 class="card-title mb-3 text-center">
                            {{ __('Edit Your Showcase!') }}
                        </h2>
                    </header>

                    <form method="POST" action="{{ route('showcase.update', $showcaseItem) }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        @php
                            $imagePath = $showcaseItem->custom_path ?? $showcaseItem->content?->path_low_res;
                        @endphp

                        <div class="mb-3 text-center">
                            <img src="{{ $imagePath ? asset('storage/' . $imagePath) : asset('aset/rupaia_logo.png') }}"
                                class="img-thumbnail" style="max-width: 300px; max-height: 300px; object-fit: cover;"
                                alt="{{ $showcaseItem->description ?? 'No caption available.' }}">
                        </div>

                        <div class="mb-3">
                            <label for="description" :value="__('Description')" class="form-label">Description</label>
                            <input id="description" name="description" type="text"
                                value="{{ old('description', $showcaseItem->description) }}"
                                class="form-control
                                @error('description') is-invalid @enderror">

                            @error('description')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                        <button class="w-100 btn btn-lg btn-dark" type="submit">
                            Update Content
                        </button>
                    </form>
                </div>
            </div>
        </div>
    @endsection
