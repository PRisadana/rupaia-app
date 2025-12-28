@extends('layouts.main')

@section('content')
    <div class="container col-xl-10 col-xxl-8 px-4 py-5">
        <div class="row align-items-center g-lg-5 py-5">
            <div class="col-lg-7 text-center text-lg-start">
                <h1 class="display-4 fw-bold lh-1 text-body-emphasis mb-3">
                    Hi Dear, Welcome Back!
                </h1>
                <p class="col-lg-10 fs-4">
                    Please enter your credentials to access your account.
                </p>
            </div>
            <div class="col-md-10 mx-auto col-lg-5">
                <form method="POST" action="{{ route('login') }}" class="p-4 p-md-5 border rounded-3 bg-body-tertiary">
                    @csrf
                    <div class="form-floating mb-3">
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                            id="email" value="{{ old('email') }}" placeholder="name@example.com" required>
                        <label for="email">Email address</label>

                        @error('email')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="form-floating mb-3">
                        <input type="password" name="password" class="form-control @error('password') is-invalid @enderror"
                            id="password" placeholder="Password" required>
                        <label for="password">Password</label>

                        @error('password')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <button class="w-100 btn btn-lg btn-dark" type="submit">
                        Sign in
                    </button>

                    <a href="{{ route('register') }}" class="nav-link px-2 text-dark mb-3 mt-3 ">Register new account</a>

                    {{-- @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="nav-link px-2 text-dark mb-3 mt-3">
                            {{ __('Forgot your password?') }}
                        </a>
                    @endif --}}
                </form>
            </div>
        </div>
    </div>
@endsection
