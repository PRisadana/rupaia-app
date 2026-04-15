@extends('layouts.admin')

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-10 ">
            <div class="card shadow-sm mb-4">
                <div class="card-body p-4 p-md-5">
                    <header>
                        <h2 class="card-title mb-3 text-center">
                            {{ __('Add Preset') }}
                        </h2>
                    </header>

                    <form method="POST" action="{{ route('admin.preset.store') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label for="preset_name" :value="__('Preset Name')" class="form-label">Preset Name</label>
                            <input id="preset_name" name="preset_name" type="text"
                                class="form-control @error('preset_name') is-invalid @enderror">

                            @error('preset_name')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="preset_file_path" class="form-label">{{ __('Preset File') }}</label>
                            <input id="preset_file_path" name="preset_file_path" type="file"
                                class="form-control @error('preset_file_path') is-invalid @enderror">
                            @error('preset_file_path')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="is_active" :value="__('Is Active')" class="form-label">Is this preset
                                active?</label>
                            <select name="is_active" id="is_active"
                                class="form-select @error('is_active') is-invalid @enderror">
                                <option value="1" {{ old('is_active') == '1' ? 'selected' : '' }}>Yes
                                </option>
                                <option value="0" {{ old('is_active', '0') == '0' ? 'selected' : '' }}>
                                    No
                                </option>
                            </select>

                            @error('is_active')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <button class="w-100 btn btn-lg btn-dark" type="submit">
                            Add Preset
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
