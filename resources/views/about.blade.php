@extends('layouts.main')

@section('content')
    <div class="px-4 py-5 my-2 text-center">
        <img class="d-block mx-auto mb-4 rounded-circle" src="{{ url('aset/rupaia_logo.png') }}" alt="Logo" width="144">
        <h1 class="display-5 fw-bold text-body-emphasis">Where Form Meets Value</h1>
        <div class="col-lg-6 mx-auto">
            <p class="lead mb-4">
                Welcome to Rupaia. We are a premium, curated marketplace born from a deep appreciation for visual beauty and
                the value of creation. We exist to bridge talented creators with professionals seeking authentic,
                high-quality visuals.
            </p>
            <hr>
            {{-- <div class="d-grid gap-2 d-sm-flex justify-content-sm-center">
                <button type="button" class="btn btn-primary btn-lg px-4 gap-3">
                    Primary button
                </button>
                <button type="button" class="btn btn-outline-secondary btn-lg px-4">
                    Secondary
                </button>
            </div> --}}
        </div>
    </div>

    <div class="px-4 pt-5 my-2 text-center border-bottom">
        <h1 class="display-4 fw-bold text-body-emphasis">
            Our Philosophy: Visuals with Value
        </h1>
        <div class="col-lg-6 mx-auto">
            <p class="lead mb-4">
                The name Rupaia is derived from two words: "Rupa," an Indonesian word representing all forms of visual
                embodiment and expression; and "-ia," an affix that lends a modern, elegant, and distinct identity.

                We believe that in the digital age, a visual is more than just an image—it is an asset, a story, and a
                representation of value. Our mission is simple: to provide a professional, trustworthy, and simple ecosystem
                where creativity can thrive and be appropriately valued.
            </p>
            {{-- <div class="d-grid gap-2 d-sm-flex justify-content-sm-center mb-5">
                <button type="button" class="btn btn-primary btn-lg px-4 me-sm-3">
                    Primary button
                </button>
                <button type="button" class="btn btn-outline-secondary btn-lg px-4">
                    Secondary
                </button>
            </div> --}}
        </div>
        <div class="overflow-hidden" style="max-height: 30vh">
            <div class="container px-5">
                <img src="{{ url('aset\about_3.jpg') }}" class="img-fluid border rounded-3 shadow-lg mb-4"
                    alt="Example image" width="700" height="500" loading="lazy">
            </div>
        </div>
    </div>

    <div class="container my-5">
        <div class="row p-4 pb-0 pe-lg-0 pt-lg-5 align-items-center rounded-3 border shadow-sm">
            <div class="col-lg-7 p-3 p-lg-5 pt-lg-3">
                <h3>For Creators</h3>
                <h1 class="display-4 fw-bold lh-1 text-body-emphasis">
                    Valuing Your Creativity
                </h1>
                <p class="lead">
                    Your work deserves the best stage. At Rupaia, we don't just offer a platform to sell; we offer a
                    community that respects originality and aesthetic value. We are committed to a transparent and
                    professional process, ensuring every piece of your work is treated with respect and receives the value
                    it deserves.
                </p>
                {{-- <div class="d-grid gap-2 d-md-flex justify-content-md-start mb-4 mb-lg-3">
                    <button type="button" class="btn btn-primary btn-lg px-4 me-md-2 fw-bold">
                        Primary
                    </button>
                    <button type="button" class="btn btn-outline-secondary btn-lg px-4">
                        Default
                    </button>
                </div> --}}
            </div>
            <div class="col-lg-4 offset-lg-1 p-0 overflow-hidden shadow-lg">
                <img class="rounded-lg-3" src="{{ url('aset/about_2.jpg') }}" alt="" width="720">
            </div>
        </div>
    </div>

    <div class="container my-5">
        <div class="row p-4 pb-0 pe-lg-0 pt-lg-5 align-items-center rounded-3 border shadow-sm">
            <div class="col-lg-4 offset-lg-1 p-0 overflow-hidden shadow-sm">
                <img class="rounded-lg-3" src="{{ url('aset/about_5.jpg') }}" alt="" width="720">
            </div>
            <div class="col-lg-7 p-3 p-lg-5 pt-lg-3">
                <h3>For Professionals & Brands</h3>
                <h1 class="display-4 fw-bold lh-1 text-body-emphasis">
                    Premium Visuals for Professional Needs
                </h1>
                <p class="lead">
                    Move beyond generic visuals. Discover a curated collection of premium photos, illustrations, and videos
                    that meet the highest quality standards. Rupaia focuses on works with an artistic touch and authentic
                    local beauty. We help you find the right "rupa" (form) to tell your brand's story elegantly and
                    effectively.
                </p>
                {{-- <div class="d-grid gap-2 d-md-flex justify-content-md-start mb-4 mb-lg-3">
                    <button type="button" class="btn btn-primary btn-lg px-4 me-md-2 fw-bold">
                        Primary
                    </button>
                    <button type="button" class="btn btn-outline-secondary btn-lg px-4">
                        Default
                    </button>
                </div> --}}
            </div>
        </div>
    </div>

    <div class="px-4 py-5 my-2 text-center">
        <h1 class="display-5 fw-bold text-body-emphasis">Join a Creative Ecosystem That Values Quality.</h1>
        <h4>Visuals with Value.</h4>
    </div>
@endsection
