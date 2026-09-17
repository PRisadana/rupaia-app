@extends('layouts.main')

@section('content')
    <div class="container-fluid px-4 admin-page-wrapper">
        <div class="row justify-content-center">
            <div class="col-12 col-lg-8 col-xl-7">
                <div class="card admin-card">
                    <div class="card-body p-4 p-md-5">
                        <div class="mb-4 text-center">
                            <h2 class="card-title mb-1">
                                {{ __('Edit Bank Account') }}
                            </h2>
                            <p class="text-muted mb-0">
                                Update bank account information.
                            </p>
                        </div>

                        @if ($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <strong>There was a problem:</strong>
                                <ul class="mb-0 mt-2">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        @if (session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('bank-accounts.update', $bankAccount) }}"
                            enctype="multipart/form-data">
                            @csrf
                            @method('PUT')

                            <div class="mb-3">
                                <label for="bank_name" :value="__('Bank Name')" class="form-label">Bank Name</label>
                                <input id="bank_name" name="bank_name" type="text"
                                    value="{{ old('bank_name', $bankAccount->bank_name) }}"
                                    class="form-control @error('bank_name') is-invalid @enderror">

                                @error('bank_name')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="account_name" :value="__('Account Name')" class="form-label">Account
                                    Name</label>
                                <input id="account_name" name="account_name" type="text"
                                    value="{{ old('account_name', $bankAccount->account_name) }}"
                                    class="form-control @error('account_name') is-invalid @enderror">

                                @error('account_name')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="account_number" :value="__('Account Number')" class="form-label">Account
                                    Number</label>
                                <input id="account_number" name="account_number" type="text"
                                    value="{{ old('account_number', $bankAccount->account_number) }}"
                                    class="form-control @error('account_number') is-invalid @enderror">

                                @error('account_number')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                            <button class="w-100 btn btn-lg btn-dark" type="submit">
                                Update Bank Account
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
