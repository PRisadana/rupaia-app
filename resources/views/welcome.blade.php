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
                    Discover authentic visual works from Indonesia's best creators.
                </h1>
                <p class="lead">
                    Explore a collection of premium photos, illustrations, and videos for your projects, or start selling
                    your work on a secure and trusted platform.
                </p>
                <form role="search">
                    <input class="form-control" type="search" placeholder="Search photos and more..." aria-label="Search ">
                </form>
            </div>
        </div>
    </div>
@endsection
