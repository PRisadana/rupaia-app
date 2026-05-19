@extends('layouts.admin')

@section('content')
    <div class="container-fluid px-4 admin-page-wrapper">
        <div class="row justify-content-center">
            <div class="col-12 col-lg-8 col-xl-7">
                <div class="card admin-card">
                    <div class="card-body p-4 p-md-5">
                        <div class="mb-4 text-center">
                            <h2 class="card-title mb-1">
                                {{ __('Edit User') }}
                            </h2>
                            <p class="text-muted mb-0">
                                Update user information.
                            </p>
                        </div>

                        <form method="POST" action="{{ route('admin.user.update', $user) }}" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')

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
                                <select name="role" id="role"
                                    class="form-select @error('role') is-invalid @enderror">
                                    <option value="admin" {{ old('role', $user->role) == 'admin' ? 'selected' : '' }}>
                                        Admin
                                    </option>
                                    <option value="buyer" {{ old('role', $user->role) == 'buyer' ? 'selected' : '' }}>
                                        Buyer
                                    </option>
                                    <option value="seller" {{ old('role', $user->role) == 'seller' ? 'selected' : '' }}>
                                        Seller
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
        </div>
    </div>
@endsection
