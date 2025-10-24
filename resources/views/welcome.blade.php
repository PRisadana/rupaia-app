@extends('layouts.main')

@section('content')
    <div class="container col-xxl-8 px-4 py-5 ">
        <div class="row flex-lg-row-reverse align-items-center justify-center g-5 py-5">
            <div class="col-10 col-sm-8 col-lg-6 items-center ">
                <img src=" {{ url('aset\welcome_image.jpg') }}  " class=" img-fluid d-block" alt="Bootstrap Themes"
                    width="700" loading="lazy">
            </div>
            <div class="col-lg-6 items-center">
                <h1 class="display-5 fw-bold text-body-emphasis lh-1 mb-3">
                    Temukan karya visual autentik dari kreator terbaik Indonesia.
                </h1>
                <p class="lead">
                    Jelajahi koleksi foto, ilustrasi, dan video premium untuk proyek Anda, atau mulai jual karya Anda di
                    platform yang aman dan terpercaya.
                </p>
                <form role="search">
                    <input class="form-control" type="search" placeholder="Search photos and more..." aria-label="Search ">
                </form>
                {{-- <div class="d-grid gap-2 d-md-flex justify-content-md-start">
                    <button type="button" class="btn btn-primary btn-lg px-4 me-md-2">
                        Primary
                    </button>
                    <button type="button" class="btn btn-outline-secondary btn-lg px-4">
                        Default
                    </button>
                </div> --}}
            </div>
        </div>
    </div>
@endsection
