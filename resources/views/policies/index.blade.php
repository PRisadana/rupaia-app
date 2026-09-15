@extends('layouts.main')

@section('content')
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="mb-4">
                    <h1 class="fw-bold">Rupaia Policies</h1>
                    <p class="text-muted">
                        This page contains the terms, privacy policy, copyright policy,
                        upload rules, license policy, and platform disclaimer for using Rupaia.
                    </p>
                </div>

                <div class="card shadow-sm mb-4">
                    <div class="card-body p-4">
                        <h3>1. Terms of Service</h3>
                        <p>
                            By using Rupaia, users agree to use the platform responsibly and comply
                            with the rules provided by the system. Users are responsible for all
                            activities performed through their accounts.
                        </p>
                        <ul>
                            <li>Users must provide valid account information.</li>
                            <li>Users must not misuse the platform for fraud, spam, or illegal activity.</li>
                            <li>Users must not interfere with the security or availability of the system.</li>
                            <li>Rupaia may restrict, suspend, or remove access if users violate platform rules.</li>
                        </ul>
                    </div>
                </div>

                <div class="card shadow-sm mb-4">
                    <div class="card-body p-4">
                        <h3>2. Upload Guidelines</h3>
                        <p>
                            Sellers are responsible for ensuring that every uploaded content is lawful,
                            appropriate, and suitable for publication on the platform.
                        </p>
                        <ul>
                            <li>Uploaded content must be a static two-dimensional digital image.</li>
                            <li>Supported formats include JPG, JPEG, and PNG.</li>
                            <li>Sellers must not upload content that infringes copyright or violates another party's rights.
                            </li>
                            <li>Sellers must not upload offensive, misleading, harmful, or prohibited content.</li>
                            <li>Sellers must provide suitable title, description, price, tags, folder, and license
                                information.</li>
                            <li>Rupaia may apply technical validation, watermarking, similarity checking, and moderation
                                review.</li>
                        </ul>
                    </div>
                </div>

                <div class="card shadow-sm mb-4">
                    <div class="card-body p-4">
                        <h3>3. Copyright Policy</h3>
                        <p>
                            Sellers must only upload content that they own or are legally allowed to distribute.
                            Uploading copyrighted material without permission is not allowed.
                        </p>
                        <ul>
                            <li>The seller remains responsible for the originality and legality of uploaded content.</li>
                            <li>Buyers or other users may report content suspected of copyright infringement.</li>
                            <li>Rupaia may review, restrict, hide, or remove reported content.</li>
                            <li>Automatic validation and moderation are preventive measures and do not guarantee that all
                                violations can be detected.</li>
                        </ul>
                    </div>
                </div>

                <div class="card shadow-sm mb-4" id="license-policy">
                    <div class="card-body p-4">
                        <h3>4. License Policy</h3>

                        <p>
                            Each content or bundle may be associated with a license that explains how the buyer
                            may use the purchased content. Buying content on Rupaia does not transfer copyright
                            ownership to the buyer. The buyer only receives usage rights based on the selected license,
                            while copyright remains with the seller or the legitimate copyright holder.
                        </p>

                        <ul>
                            <li>Content inside a collection folder uses its own content license.</li>
                            <li>Content inside a bundle folder follows the bundle folder license.</li>
                            <li>If a bundle allows individual sale, the content may still be purchased individually using
                                the same bundle license.</li>
                            <li>If a bundle does not allow individual sale, the content is only available as part of the
                                bundle.</li>
                            <li>Buyers must follow the license terms shown on the content or bundle page.</li>
                        </ul>

                        <div class="alert alert-warning mt-3">
                            <strong>Important:</strong>
                            Buyers are not allowed to resell the original file as a standalone asset,
                            redistribute the file to other parties, claim ownership or copyright over the seller's work,
                            remove ownership information or metadata, or use the content for illegal activities.
                        </div>

                        <h4 class="h5 mt-4 mb-3">Available License Types</h4>

                        @if ($licenses->isEmpty())
                            <div class="alert alert-light border mb-0">
                                No active licenses are available at the moment.
                            </div>
                        @else
                            <div class="row g-3">
                                @foreach ($licenses as $license)
                                    <div class="col-md-6">
                                        <div class="border rounded p-3 h-100">
                                            <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                                <h5 class="mb-0">{{ $license->name }}</h5>

                                                @if ($license->code)
                                                    <span class="badge bg-dark">
                                                        {{ strtoupper(str_replace('_', ' ', $license->code)) }}
                                                    </span>
                                                @endif
                                            </div>

                                            @if ($license->description)
                                                <p class="text-muted mb-3">
                                                    {{ $license->description }}
                                                </p>
                                            @else
                                                <p class="text-muted mb-3">
                                                    No description has been added for this license.
                                                </p>
                                            @endif

                                            @if ($license->terms)
                                                <div class="small mb-0">
                                                    {!! nl2br(e($license->terms)) !!}
                                                </div>
                                            @else
                                                <div class="small text-muted mb-0">
                                                    Detailed terms for this license have not been added yet.
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>

                <div class="card shadow-sm mb-4">
                    <div class="card-body p-4">
                        <h3>5. Privacy Policy</h3>
                        <p>
                            Rupaia processes user data only as needed to provide, secure, and improve
                            the platform.
                        </p>
                        <ul>
                            <li>Account data may include name, email, profile information, and encrypted credentials.</li>
                            <li>Uploaded content, metadata, tags, reports, and activity logs may be processed by the system.
                            </li>
                            <li>Data may be used for authentication, content management, moderation, reporting, security,
                                and system improvement.</li>
                            <li>Access to data is limited based on user roles and system needs.</li>
                        </ul>
                    </div>
                </div>

                <div class="card shadow-sm mb-4">
                    <div class="card-body p-4">
                        <h3>6. Platform Disclaimer</h3>
                        <p>
                            Rupaia provides tools for publishing, organizing, displaying, licensing,
                            and managing digital visual content. However, the platform does not guarantee
                            that every uploaded content is original, dispute-free, or free from potential infringement.
                        </p>
                        <ul>
                            <li>Watermarking, validation, report handling, and moderation are preventive controls.</li>
                            <li>These controls do not completely eliminate the possibility of misuse or infringement.</li>
                            <li>Users remain responsible for uploaded content and actions performed through their accounts.
                            </li>
                            <li>Rupaia may take action against content or accounts that violate platform rules.</li>
                        </ul>
                    </div>
                </div>

                <div class="alert alert-dark">
                    By creating an account, uploading content, reporting content, or using Rupaia,
                    users confirm that they have read, understood, and agreed to these policies.
                </div>
            </div>
        </div>
    </div>
@endsection
