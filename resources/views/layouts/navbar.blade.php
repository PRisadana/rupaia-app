<header class="p-3 text-white bg-dark">
    <div class="container">
        <div class="d-flex flex-wrap align-items-center justify-content-center justify-content-lg-start">
            <a href="/" class="d-flex align-items-center mb-2 mb-lg-0 text-white text-decoration-none">
                <img src="{{ url('aset/rupaia_logo.png') }}" alt="Logo" class="rounded-circle" width="50"
                    class="d-inline-block align-text-top">
            </a>
            <ul class="nav col-12 col-lg-auto me-lg-auto mb-2 justify-content-center mb-md-0">
                {{-- <li><a href="/" class="nav-link px-2 text-white">Home</a></li> --}}
                <li><a href="/about" class="nav-link px-2 text-white">About</a></li>
                <li><a href="#" class="nav-link px-2 text-white">License</a></li>
            </ul>
            {{-- <form class="col-12 col-lg-auto mb-3 mb-lg-0 me-lg-3" role="search">
                <input type="search" class="form-control form-control-dark text-bg-dark" placeholder="Search..."
                    aria-label="Search">
            </form> --}}
            @auth
                <li class="me-lg mb-2 mb-md-0 list-unstyled">
                    <span class="nav-link">Hi, {{ Auth::user()->name }}</span>
                </li>

                <li class="px-2 list-unstyled dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="profileDropdown" role="button"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        <img src="{{ Auth::user()->profile_photo_path ? asset('storage/' . Auth::user()->profile_photo_path) : asset('aset/rupaia_logo.png') }}"
                            alt="Profile" class="rounded-circle" width="30" height="30" style="object-fit: cover;">
                    </a>

                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="profileDropdown">

                        <li>
                            <a class="dropdown-item" href="#">
                                {{ __('Your Profile') }}
                            </a>
                        </li>

                        <li>
                            <a class="dropdown-item" href="{{ route('profile.edit') }}">
                                {{ __('Setting') }}
                            </a>
                        </li>

                        <li>
                            <hr class="dropdown-divider">
                        </li>

                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <a class="dropdown-item" href="{{ route('dashboard') }}"
                                    onclick="event.preventDefault(); this.closest('form').submit();">
                                    {{ __('Log Out') }}
                                </a>
                            </form>
                        </li>
                    </ul>
                </li>
            @else
                <div class="text-end">
                    <button type="button" class="btn btn-outline-light me-2">
                        <a href="/login" class="nav-link px-2">Login</a>
                    </button>
                </div>
            @endauth
        </div>
    </div>
</header>
