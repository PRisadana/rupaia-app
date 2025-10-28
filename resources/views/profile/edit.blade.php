@extends('layouts.main')

@section('content')
    <div class="row justify-content-center">

        <div class="col-lg-10">
            <div class="card shadow-sm mb-4">
                <div class="card-body p-4 p-md-5">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>
        </div>

        <div class="col-lg-10">
            <div class="card shadow-sm mb-4">
                <div class="card-body p-4 p-md-5">
                    @include('profile.partials.update-password-form')
                </div>
            </div>
        </div>

        <div class="col-lg-10">
            <div class="card shadow-sm mb-4 border-danger">
                <div class="card-body p-4 p-md-5">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    </div>
    </div>
@endsection
