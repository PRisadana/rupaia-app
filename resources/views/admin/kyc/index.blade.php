@extends('layouts.admin')

@section('content')
    <div class="container-fluid px-4 admin-page-wrapper">
        <div class="d-flex justify-content-between align-items-center my-4 admin-page-title">
            <div>
                <h1 class="m-0">KYC Submissions</h1>
                <p class="text-muted mb-0">Manage and review seller verification requests</p>
            </div>
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

        <div class="mb-3 d-flex flex-wrap gap-2">
            <a href="{{ route('admin.kyc.index') }}"
                class="btn btn-sm {{ !$status ? 'btn-secondary' : 'btn-outline-secondary' }}">
                All
            </a>
            <a href="{{ route('admin.kyc.index', ['status' => 'pending']) }}"
                class="btn btn-sm {{ $status === 'pending' ? 'btn-warning' : 'btn-outline-warning' }}">
                Pending
            </a>
            <a href="{{ route('admin.kyc.index', ['status' => 'verified']) }}"
                class="btn btn-sm {{ $status === 'verified' ? 'btn-success' : 'btn-outline-success' }}">
                Verified
            </a>
            <a href="{{ route('admin.kyc.index', ['status' => 'rejected']) }}"
                class="btn btn-sm {{ $status === 'rejected' ? 'btn-danger' : 'btn-outline-danger' }}">
                Rejected
            </a>
        </div>

        <div class="admin-table-wrapper">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th scope="col">No</th>
                            <th scope="col">User</th>
                            <th scope="col">Full Name</th>
                            <th scope="col">NIK</th>
                            <th scope="col">Status</th>
                            <th scope="col">Submitted At</th>
                            <th scope="col">Processed By</th>
                            <th scope="col">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($submissions as $submission)
                            <tr>
                                <td>
                                    {{ $submissions->firstItem() + $loop->index }}
                                </td>
                                <td>
                                    {{ $submission->user->name }}
                                    <div class="small text-muted">
                                        {{ $submission->user->email }}
                                    </div>
                                </td>
                                <td>
                                    {{ $submission->full_name }}
                                </td>
                                <td>
                                    {{ $submission->id_number }}
                                </td>
                                <td>
                                    @if ($submission->status === 'pending')
                                        <span class="badge bg-warning text-dark">
                                            Pending
                                        </span>
                                    @elseif ($submission->status === 'verified')
                                        <span class="badge bg-success">
                                            Verified
                                        </span>
                                    @elseif ($submission->status === 'rejected')
                                        <span class="badge bg-danger">
                                            Rejected
                                        </span>
                                    @else
                                        <span class="badge bg-secondary">
                                            {{ ucfirst($submission->status) }}
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    {{ $submission->submitted_at?->format('d M Y H:i') }}
                                </td>
                                <td>
                                    @if ($submission->processor)
                                        {{ $submission->processor->name }}
                                    @else
                                        <span class="text-muted">
                                            -
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('admin.kyc.show', $submission) }}"
                                        class="btn btn-sm btn-outline-primary" title="Review KYC">
                                        <i class="fi fi-rr-edit"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center">
                                    No KYC submissions found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                {{ $submissions->links() }}
            </div>
        </div>
    </div>
@endsection
