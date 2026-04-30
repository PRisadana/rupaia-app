@extends('layouts.admin')

@section('content')
    <div class="row justify-content-center my-4">
        <div class="col-lg-10 ">
            <div class="card shadow-sm mb-4">
                <div class="card-body p-4 p-md-5">
                    <header>
                        <h2 class="card-title mb-3 text-center">
                            {{ __('Edit User') }}
                        </h2>
                    </header>

                    <form method="POST" action="{{ route('admin.user.update', $user) }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        {{-- <div class="mb-3">
                            <label class="form-label">
                                <h4>Preset File Path</h4>
                            </label>
                            <div class="form-control-plaintext">
                                {{ $preset->preset_file_path }}
                            </div>
                        </div> --}}

                        <div class="mb-3">
                            <label for="name" :value="__('Name')" class="form-label">Name</label>
                            <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}"
                                class="form-control @error('name') is-invalid @enderror">

                            @error('name')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="email" :value="__('Email')" class="form-label">Email</label>
                            <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}"
                                class="form-control @error('email') is-invalid @enderror">

                            @error('email')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">New Password</label>
                            <input type="password" name="password" id="password"
                                class="form-control @error('password') is-invalid @enderror">
                            <small class="text-muted">Leave blank if you do not want to change the password.</small>

                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-floating mb-3">
                            <input type="password" name="password_confirmation" class="form-control"
                                id="password_confirmation">
                            <label for="password_confirmation">Confirm Password</label>
                        </div>

                        <div class="mb-3">
                            <label for="role" :value="__('Role')" class="form-label">Role</label>
                            <select name="role" id="role" class="form-select @error('role') is-invalid @enderror">
                                <option value="admin" {{ old('role', $user->role) == 'admin' ? 'selected' : '' }}>Admin
                                </option>
                                <option value="buyer" {{ old('role', $user->role) == 'buyer' ? 'selected' : '' }}>Buyer
                                </option>
                                <option value="seller" {{ old('role', $user->role) == 'seller' ? 'selected' : '' }}>Seller
                                </option>
                            </select>

                            @error('role')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <button class="w-100 btn btn-lg btn-dark" type="submit">
                            Update User
                        </button>

                    </form>
                </div>
            </div>
        </div>
    @endsection
