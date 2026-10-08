@extends('layouts.main')

@section('content')
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-1">
                    My KYC Data
                </h2>

                <p class="text-muted mb-0">
                    Your verified identity information.
                </p>
            </div>

            <a href="{{ route('kyc.index') }}" class="btn btn-outline-secondary">
                Back
            </a>
        </div>

        <div class="alert alert-success">
            <strong>Verified</strong>
            <div>
                This information has been verified and
                cannot be edited.
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-7">
                <div class="card">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">
                            Identity Information
                        </h5>
                    </div>

                    <div class="card-body">
                        <div class="mb-3">
                            <div class="text-muted small">
                                Full Name
                            </div>
                            <strong>
                                {{ $kyc->full_name }}
                            </strong>
                        </div>
                        <hr>
                        <div class="mb-3">
                            <div class="text-muted small">
                                NIK
                            </div>
                            <strong>
                                {{ $kyc->id_number }}
                            </strong>
                        </div>
                        <hr>
                        <div class="mb-3">
                            <div class="text-muted small">
                                Status
                            </div>
                            <span class="badge bg-success">
                                Verified
                            </span>
                        </div>
                        <hr>
                        <div class="mb-3">
                            <div class="text-muted small">
                                Submitted At
                            </div>
                            <strong>
                                {{ $kyc->submitted_at ? $kyc->submitted_at->format('d M Y H:i') : '-' }}
                            </strong>
                        </div>
                        <hr>
                        <div class="mb-3">
                            <div class="text-muted small">
                                Verified At
                            </div>
                            <strong>
                                {{ $kyc->processed_at ? $kyc->processed_at->format('d M Y H:i') : '-' }}
                            </strong>
                        </div>
                        <hr>
                        <div class="mb-3">
                            <div class="text-muted small">
                                KYC Terms Accepted At
                            </div>
                            <strong>
                                {{ $kyc->terms_accepted_at ? $kyc->terms_accepted_at->format('d M Y H:i') : '-' }}
                            </strong>
                        </div>
                        <hr>
                        <div>
                            <div class="text-muted small">
                                Policy Version
                            </div>
                            <strong>
                                {{ $kyc->policy_version ?? '-' }}
                            </strong>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="card">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">
                            Identity Card
                        </h5>
                    </div>
                    <div class="card-body text-center">
                        <img src="{{ route('kyc.seller.document', $kyc) }}" alt="Verified Identity Card"
                            class="img-fluid rounded border"
                            style="
                                max-height: 420px;
                                object-fit: contain;
                            ">
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
