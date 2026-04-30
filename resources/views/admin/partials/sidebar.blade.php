<div id="layoutSidenav">
    <div id="layoutSidenav_nav">
        <nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">
            <div class="sb-sidenav-menu">
                <div class="nav">
                    <div class="sb-sidenav-menu-heading">General Information</div>
                    <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"
                        href="{{ route('admin.dashboard') }}">
                        <div class="sb-nav-link-icon d-inline-flex align-items-center"><i
                                class="fi fi-rr-chart-histogram mt-1"></i></div>
                        Dashboard
                    </a>
                    {{-- <div class="sb-sidenav-menu-heading">Interface</div>
                    <a class="nav-link collapsed" href="#" data-bs-toggle="collapse"
                        data-bs-target="#collapseLayouts" aria-expanded="false" aria-controls="collapseLayouts">
                        <div class="sb-nav-link-icon"><i class="fas fa-columns"></i></div>
                        Layouts
                        <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                    </a>
                    <div class="collapse" id="collapseLayouts" aria-labelledby="headingOne"
                        data-bs-parent="#sidenavAccordion">
                        <nav class="sb-sidenav-menu-nested nav">
                            <a class="nav-link" href="layout-static.html">Static Navigation</a>
                            <a class="nav-link" href="layout-sidenav-light.html">Light Sidenav</a>
                        </nav>
                    </div>
                    <a class="nav-link collapsed" href="#" data-bs-toggle="collapse"
                        data-bs-target="#collapsePages" aria-expanded="false" aria-controls="collapsePages">
                        <div class="sb-nav-link-icon"><i class="fas fa-book-open"></i></div>
                        Pages
                        <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                    </a>
                    <div class="collapse" id="collapsePages" aria-labelledby="headingTwo"
                        data-bs-parent="#sidenavAccordion">
                        <nav class="sb-sidenav-menu-nested nav accordion" id="sidenavAccordionPages">
                            <a class="nav-link collapsed" href="#" data-bs-toggle="collapse"
                                data-bs-target="#pagesCollapseAuth" aria-expanded="false"
                                aria-controls="pagesCollapseAuth">
                                Authentication
                                <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                            </a>
                            <div class="collapse" id="pagesCollapseAuth" aria-labelledby="headingOne"
                                data-bs-parent="#sidenavAccordionPages">
                                <nav class="sb-sidenav-menu-nested nav">
                                    <a class="nav-link" href="login.html">Login</a>
                                    <a class="nav-link" href="register.html">Register</a>
                                    <a class="nav-link" href="password.html">Forgot Password</a>
                                </nav>
                            </div>
                            <a class="nav-link collapsed" href="#" data-bs-toggle="collapse"
                                data-bs-target="#pagesCollapseError" aria-expanded="false"
                                aria-controls="pagesCollapseError">
                                Error
                                <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                            </a>
                            <div class="collapse" id="pagesCollapseError" aria-labelledby="headingOne"
                                data-bs-parent="#sidenavAccordionPages">
                                <nav class="sb-sidenav-menu-nested nav">
                                    <a class="nav-link" href="401.html">401 Page</a>
                                    <a class="nav-link" href="404.html">404 Page</a>
                                    <a class="nav-link" href="500.html">500 Page</a>
                                </nav>
                            </div>
                        </nav>
                    </div> --}}
                    <div class="sb-sidenav-menu-heading">Others</div>
                    <a class="nav-link {{ request()->routeIs('admin.user.*') ? 'active' : '' }}"
                        href="{{ route('admin.user.index') }}">
                        <div class="sb-nav-link-icon d-inline-flex align-items-center"><i
                                class="fi fi-rr-user mt-1"></i></div>
                        Users
                    </a>
                    <a class="nav-link {{ request()->routeIs('admin.content.*') ? 'active' : '' }}"
                        href="{{ route('admin.content.index') }}">
                        <div class="sb-nav-link-icon d-inline-flex align-items-center"><i
                                class="fi fi-rr-picture mt-1"></i></div>
                        Contents
                    </a>
                    <a class="nav-link {{ request()->routeIs('admin.showcase.*') ? 'active' : '' }}"
                        href="{{ route('admin.showcase.index') }}">
                        <div class="sb-nav-link-icon d-inline-flex align-items-center"><i
                                class="fi fi-rr-file-image mt-1"></i></div>
                        Showcases
                    </a>
                    <a class="nav-link" href="#">
                        <div class="sb-nav-link-icon d-inline-flex align-items-center"><i
                                class="fi fi-rr-assept-document mt-1"></i></div>
                        KYC Verification
                    </a>
                    <a class="nav-link" href="#">
                        <div class="sb-nav-link-icon d-inline-flex align-items-center"><i
                                class="fi fi-rr-exchange mt-1"></i></div>
                        Transaction
                    </a>
                    <a class="nav-link" href="#">
                        <div class="sb-nav-link-icon d-inline-flex align-items-center"><i
                                class="fi fi-rr-wallet mt-1"></i></div>
                        Payout
                    </a>
                    <a class="nav-link" href="#">
                        <div class="sb-nav-link-icon d-inline-flex align-items-center"><i
                                class="fi fi-rr-flag mt-1"></i></div>
                        Reported Content
                    </a>
                    <a class="nav-link {{ request()->routeIs('admin.preset.*') ? 'active' : '' }}"
                        href="{{ route('admin.preset.index') }}">
                        <div class="sb-nav-link-icon d-inline-flex align-items-center"><i
                                class="fi fi-rr-palette mt-1"></i></div>
                        Presets
                    </a>
                    <a class="nav-link {{ request()->routeIs('admin.tag.*') ? 'active' : '' }}"
                        href="{{ route('admin.tag.index') }}">
                        <div class="sb-nav-link-icon d-inline-flex align-items-center"><i
                                class="fi fi-rr-tags mt-1"></i></div>
                        Tags
                    </a>
                </div>
            </div>

            <div class="sb-sidenav-footer">
                <ul class="navbar-nav ms-auto ms-md-0 me-3 me-lg-4">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center gap-2" id="navbarDropdown"
                            href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <img src="{{ Auth::user()->profile_photo_path ? asset('storage/' . Auth::user()->profile_photo_path) : asset('aset/rupaia_logo.png') }}"
                                alt="Profile" class="rounded-circle" width="30" height="30"
                                style="object-fit: cover;">
                            <span class="d-none d-md-inline text-white">{{ Auth::user()->name ?? 'Admin' }}</span>
                        </a>

                        <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2"
                            aria-labelledby="navbarDropdown">
                            <li>
                                <h6 class="dropdown-header">Admin Menu</h6>
                            </li>
                            <li>
                                <a class="dropdown-item py-2 d-flex align-items-center gap-2"
                                    href="{{ route('profile.edit') }}">
                                    <i class="fi fi-rr-settings mt-1"></i>
                                    <span>Settings</span>
                                </a>
                            </li>
                            <hr class="dropdown-divider my-1" />
                    </li>

                    <li>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit"
                                class="dropdown-item py-2 d-flex align-items-center gap-2 text-danger fw-semibold">
                                <i class="fi fi-rr-sign-out-alt mt-1"></i> Logout
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </nav>
    </div>
