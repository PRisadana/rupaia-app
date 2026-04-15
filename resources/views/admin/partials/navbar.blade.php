<nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark shadow-sm px-3 px-lg-4">

    <button class="btn btn-link btn-sm order-1 order-lg-0 me-3 p-0" id="sidebarToggle" href="#!">
        <i class="fas fa-bars fs-5 text-white-50"></i>
    </button>

    <a class="navbar-brand d-flex align-items-center m-0" href="{{ route('admin.dashboard') }}">
        <span class="fs-5 fw-semibold text-white tracking-wide">Admin Panel</span>
    </a>

    <a href="/" class="d-flex align-items-center ms-auto text-decoration-none" title="Back to Homepage">
        <img src="{{ asset('aset/rupaia_logo.png') }}" alt="Logo Rupaia"
            class="rounded-circle border border-2 border-secondary" width="36" height="36"
            style="object-fit: cover; transition: transform 0.2s ease;">
    </a>

</nav>
