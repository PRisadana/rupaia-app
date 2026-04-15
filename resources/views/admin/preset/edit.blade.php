@extends('layouts.admin')

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-10 ">
            <div class="card shadow-sm mb-4">
                <div class="card-body p-4 p-md-5">
                    <header>
                        <h2 class="card-title mb-3 text-center">
                            {{ __('Edit Preset') }}
                        </h2>
                    </header>

                    <form method="POST" action="{{ route('admin.preset.update', $preset) }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label class="form-label">
                                <h4>Preset File Path</h4>
                            </label>
                            <div class="form-control-plaintext">
                                {{ $preset->preset_file_path }}
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="preset_name" :value="__('Preset Name')" class="form-label">Preset Name</label>
                            <input id="preset_name" name="preset_name" type="text"
                                value="{{ old('preset_name', $preset->preset_name) }}"
                                class="form-control @error('preset_name') is-invalid @enderror">

                            @error('preset_name')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="is_active" :value="__('Is Active')" class="form-label">Is Active</label>
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
                            Update Preset
                        </button>
                    </form>
                </div>
            </div>
        </div>
    @endsection
