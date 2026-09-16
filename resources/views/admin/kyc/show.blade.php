@extends('layouts.admin')

@section('content')
    <div class="container-fluid px-4 admin-page-wrapper">
        <div class="row justify-content-center">
            <div class="col-12 col-lg-8 col-xl-7">
                <div class="card admin-card">
                    <div class="card-body p-4 p-md-5">
                        <div class="mb-4 text-center">
                            <h2 class="card-title mb-1">
                                {{ __('Review KYC Submission') }}
                            </h2>
                            <p class="text-muted mb-0">
                                Review seller identity verification information.
                            </p>
                        </div>

                        @if (session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert">
                                </button>
                            </div>
                        @endif

                        @if ($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <strong>Action failed:</strong>
                                <ul class="mb-0 mt-2">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                                <button type="button" class="btn-close" data-bs-dismiss="alert">
                                </button>
                            </div>
                        @endif

                        <div class="mb-4">
                            <label class="form-label">
                                <h4>Identity Card</h4>
                            </label>
                            <div class="border rounded-3 p-2 bg-light text-center">
                                <img src="{{ route('admin.kyc.document', $kyc) }}" alt="Identity Card"
                                    class="img-fluid rounded" style="max-height: 320px; object-fit: contain;">
                            </div>

                        </div>

                        <div class="mb-4">
                            <label class="form-label">
                                <h4>Submission Information</h4>
                            </label>
                            <div class="border rounded-3 p-3 bg-light">
                                <div class="mb-3">
                                    <strong>User:</strong>
                                    <div class="text-muted">
                                        {{ $kyc->user->name }}
                                    </div>
                                    <div class="small text-muted">
                                        {{ $kyc->user->email }}
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <strong>Full Name:</strong>
                                    <div class="text-muted">
                                        {{ $kyc->full_name }}
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <strong>NIK:</strong>
                                    <div class="text-muted">
                                        {{ $kyc->id_number }}
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <strong>Status:</strong>
                                    <div class="mt-1">
                                        @if ($kyc->status === 'pending')
                                            <span class="badge bg-warning text-dark">
                                                Pending
                                            </span>
                                        @elseif ($kyc->status === 'verified')
                                            <span class="badge bg-success">
                                                Verified
                                            </span>
                                        @elseif ($kyc->status === 'rejected')
                                            <span class="badge bg-danger">
                                                Rejected
                                            </span>
                                        @else
                                            <span class="badge bg-secondary">
                                                {{ ucfirst($kyc->status) }}
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <strong>Submitted At:</strong>
                                    <div class="text-muted">
                                        {{ $kyc->submitted_at?->format('d M Y H:i') ?? '-' }}
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <strong>Seller Policy Version:</strong>
                                    <div class="text-muted">
                                        {{ $kyc->policy_version }}
                                    </div>
                                </div>

                                <div>
                                    <strong>Terms Accepted At:</strong>
                                    <div class="text-muted">
                                        {{ $kyc->terms_accepted_at?->format('d M Y H:i') ?? '-' }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        @if ($kyc->status !== 'pending')
                            <div class="mb-4">
                                <label class="form-label">
                                    <h4>Review Result</h4>
                                </label>
                                <div class="border rounded-3 p-3 bg-light">
                                    <div class="mb-3">
                                        <strong>Processed By:</strong>
                                        <div class="text-muted">
                                            {{ $kyc->processor?->name ?? '-' }}
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <strong>Processed At:</strong>
                                        <div class="text-muted">
                                            {{ $kyc->processed_at?->format('d M Y H:i') ?? '-' }}
                                        </div>
                                    </div>

                                    <div>
                                        <strong>Admin Note:</strong>
                                        <div class="text-muted">
                                            {{ $kyc->admin_note ?? '-' }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if ($kyc->status === 'pending')
                            <div class="mb-4">
                                <label class="form-label">
                                    <h4>Review Decision</h4>
                                </label>
                                <div class="border rounded-3 p-3 bg-light">

                                    <form method="POST" action="{{ route('admin.kyc.verify', $kyc) }}" class="mb-4">
                                        @csrf
                                        @method('PATCH')
                                        <div class="alert alert-success">
                                            Verify this submission if the identity information
                                            and document are considered valid.
                                        </div>
                                        <button type="submit" class="w-100 btn btn-lg btn-success"
                                            onclick="return confirm('Are you sure you want to verify this KYC submission?')">
                                            Verify KYC
                                        </button>
                                    </form>
                                    <hr class="my-4">

                                    <form method="POST" action="{{ route('admin.kyc.reject', $kyc) }}">
                                        @csrf
                                        @method('PATCH')
                                        <div class="mb-3">
                                            <label for="admin_note" class="form-label">
                                                Rejection Reason
                                            </label>
                                            <textarea name="admin_note" id="admin_note" rows="4"
                                                class="form-control @error('admin_note') is-invalid @enderror"
                                                placeholder="Write the reason why this submission is rejected.">{{ old('admin_note') }}</textarea>
                                            @error('admin_note')
                                                <div class="invalid-feedback">
                                                    {{ $message }}
                                                </div>
                                            @enderror

                                            <div class="form-text">
                                                The rejection reason will be shown to the buyer
                                                so the information can be corrected before resubmission.
                                            </div>
                                        </div>
                                        <button type="submit" class="w-100 btn btn-lg btn-danger"
                                            onclick="return confirm('Are you sure you want to reject this KYC submission?')">
                                            Reject KYC
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
