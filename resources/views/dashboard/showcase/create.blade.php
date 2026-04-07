@extends('layouts.main')

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-10 ">
            <div class="card shadow-sm mb-4">
                <div class="card-body p-4 p-md-5">
                    <header>
                        <h2 class="card-title mb-3 text-center">
                            {{ __('Upload Your Showcase!') }}
                        </h2>
                    </header>

                    <form method="POST" action="{{ route('showcase.store') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label for="description" :value="__('Showcase Description')" class="form-label">Showcase
                                Description</label>
                            <input id="description" name="description" type="text"
                                class="form-control @error('description') is-invalid @enderror">

                            @error('description')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="custom_path" class="form-label">{{ __('Showcase Image') }}</label>
                            <input id="custom_path" name="custom_path" type="file"
                                class="form-control @error('custom_path') is-invalid @enderror">
                            @error('custom_path')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
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
