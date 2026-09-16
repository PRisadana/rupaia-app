@extends('layouts.main')

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-10 ">
            <div class="card shadow-sm mb-4">
                <div class="card-body p-4 p-md-5">
                    <header>
                        <h2 class="card-title mb-3 text-center">
                            {{ __('Fill Your Personal Information') }}
                        </h2>
                    </header>

                    {{-- @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <strong>There was a problem:</strong>
                            <ul class="mb-0 mt-2">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif --}}

                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('kyc.store') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">
                                Full Name
                            </label>
                            <input type="text" name="full_name"
                                class="form-control @error('full_name') is-invalid @enderror"
                                value="{{ old('full_name', $latestSubmission?->full_name) }}" required>

                            @error('full_name')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">
                                Nomor Induk Kependudukan (NIK)
                            </label>
                            <input type="text" name="id_number" maxlength="16" inputmode="numeric"
                                class="form-control @error('id_number') is-invalid @enderror"
                                value="{{ old('id_number', $latestSubmission?->id_number) }}" required>
                            @error('id_number')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">
                                Identity Card
                            </label>
                            <input type="file" id="id_card" name="id_card" accept=".jpg,.jpeg,.png"
                                class="form-control @error('id_card') is-invalid @enderror" required>
                            <div class="form-text">
                                JPG, JPEG, or PNG. Maximum 2 MB.
                            </div>
                            @error('id_card')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                            <p class="small text-muted mb-2">
                                KTP, SIM, or Passport. Make sure the image is clear and all information is readable.
                            </p>
                        </div>

                        <div id="identity-preview-container" class="d-none mt-3">
                            <p class="small text-muted mb-2">
                                Identity Card Preview
                            </p>
                            <img id="identity-preview" class="img-fluid border rounded" style="max-height: 320px;"
                                alt="Identity Card Preview">
                        </div>

                        <div class="form-check mt-4">
                            <input type="checkbox" class="form-check-input" name="data_truth_confirmation" value="1"
                                id="data_truth_confirmation" {{ old('data_truth_confirmation') ? 'checked' : '' }}>
                            <label class="form-check-label" for="data_truth_confirmation">
                                I confirm that the information and identity
                                document submitted are correct and belong to me.
                            </label>
                        </div>
                        @error('data_truth_confirmation')
                            <div class="text-danger small mt-1">
                                {{ $message }}
                            </div>
                        @enderror

                        <div class="form-check mt-3">
                            <input type="checkbox" class="form-check-input" name="terms_accepted" value="1"
                                id="terms_accepted" {{ old('terms_accepted') ? 'checked' : '' }}>
                            <label class="form-check-label" for="terms_accepted">
                                I agree to the Seller Terms and the processing
                                of my KYC information.
                            </label>
                        </div>
                        @error('terms_accepted')
                            <div class="text-danger small mt-1">
                                {{ $message }}
                            </div>
                        @enderror

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">
                                Submit Verification
                            </button>
                            <a href="{{ route('kyc.index') }}" class="btn btn-outline-secondary">
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {

                const input = document.getElementById('id_card');
                const preview = document.getElementById('identity-preview');
                const container = document.getElementById(
                    'identity-preview-container'
                );

                input.addEventListener('change', function() {

                    const file = this.files[0];

                    if (!file) {
                        preview.src = '';
                        container.classList.add('d-none');
                        return;
                    }

                    const previewUrl = URL.createObjectURL(file);

                    preview.src = previewUrl;
                    container.classList.remove('d-none');

                    preview.onload = function() {
                        URL.revokeObjectURL(previewUrl);
                    };
                });

            });
        </script>
    @endpush
@endsection
