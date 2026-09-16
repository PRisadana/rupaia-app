@extends('layouts.main')

@section('content')
    <div class="container py-4">
        <div class="mb-4">
            <h2>Seller Verification</h2>
            <p class="text-muted">
                Verify your identity to obtain seller access.
            </p>
        </div>

        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if (session('info'))
            <div class="alert alert-info">
                {{ session('info') }}
            </div>
        @endif

        @if ($errors->has('kyc'))
            <div class="alert alert-danger">
                {{ $errors->first('kyc') }}
            </div>
        @endif

        @if (!$latestSubmission)
            <div class="card">
                <div class="card-body">
                    <h5>Become a Seller</h5>
                    <p>
                        Complete identity verification to start
                        selling content on Rupaia.
                    </p>
                    <a href="{{ route('kyc.create') }}" class="btn btn-primary">
                        Submit Verification
                    </a>
                </div>
            </div>
        @elseif ($latestSubmission->status === 'pending')
            <div class="card">
                <div class="card-body">
                    <span class="badge bg-warning text-dark mb-3">
                        Pending Review
                    </span>
                    <h5>
                        Your verification is being reviewed.
                    </h5>
                    <p class="text-muted mb-0">
                        Submitted:
                        {{ $latestSubmission->submitted_at->format('d M Y H:i') }}
                    </p>
                </div>
            </div>
        @elseif ($latestSubmission->status === 'rejected')
            <div class="card">
                <div class="card-body">
                    <span class="badge bg-danger mb-3">
                        Rejected
                    </span>
                    <h5>
                        Your seller verification was rejected.
                    </h5>
                    <div class="alert alert-danger mt-3">
                        <strong>Reason:</strong>
                        <div>
                            {{ $latestSubmission->admin_note ?? 'No reason provided.' }}
                        </div>
                    </div>
                    <p>
                        Correct the required information and submit
                        a new verification request.
                    </p>
                    <a href="{{ route('kyc.create') }}" class="btn btn-primary">
                        Submit Again
                    </a>
                </div>
            </div>
        @elseif ($latestSubmission->status === 'verified')
            <div class="card">
                <div class="card-body">
                    <span class="badge bg-success mb-3">
                        Verified
                    </span>
                    <h5>
                        Your seller verification has been approved.
                    </h5>
                    <p class="mb-0">
                        Your account now has seller access.
                    </p>
                </div>
            </div>
        @endif
    </div>
@endsection
