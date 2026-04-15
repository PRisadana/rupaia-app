@extends('layouts.admin')

@section('content')
    <div class="container-fluid px-4">
        <h1 class="my-4">Dashboard</h1>
        <hr>
        <div class="row my-4">
            <div class="col-xl-3 col-md-6">
                <div class="card text-bg-light mb-4" style="max-width: 18rem;">
                    <div class="card-header">Total Users</div>
                    <div class="card-body">
                        <h5 class="card-title">{{ $totalUsers }}</h5>
                        <p class="card-text">Total number of users in the system.</p>
                    </div>
                    <div class="card-footer d-flex align-items-center justify-content-between">
                        <a class="small text-dark stretched-link" href="#">View Details</a>
                        <div class="small text-dark"><i class="fas fa-angle-right"></i></div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card text-bg-light mb-4" style="max-width: 18rem;">
                    <div class="card-header">Total Contents</div>
                    <div class="card-body">
                        <h5 class="card-title">{{ $totalContents }}</h5>
                        <p class="card-text">Total number of contents in the system.</p>
                    </div>
                    <div class="card-footer d-flex align-items-center justify-content-between">
                        <a class="small text-dark stretched-link" href="#">View Details</a>
                        <div class="small text-dark"><i class="fas fa-angle-right"></i></div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card text-bg-light mb-4" style="max-width: 18rem;">
                    <div class="card-header">Total Showcases</div>
                    <div class="card-body">
                        <h5 class="card-title">{{ $totalShowcaseItems }}</h5>
                        <p class="card-text">Total number of showcases in the system.</p>
                    </div>
                    <div class="card-footer d-flex align-items-center justify-content-between">
                        <a class="small text-dark stretched-link" href="#">View Details</a>
                        <div class="small text-dark"><i class="fas fa-angle-right"></i></div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card text-bg-light mb-4" style="max-width: 18rem;">
                    <div class="card-header">Total Presets</div>
                    <div class="card-body">
                        <h5 class="card-title">{{ $totalPresets }}</h5>
                        <p class="card-text">Total number of presets in the system.</p>
                    </div>
                    <div class="card-footer d-flex align-items-center justify-content-between">
                        <a class="small text-dark stretched-link" href="#">View Details</a>
                        <div class="small text-dark"><i class="fas fa-angle-right"></i></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
