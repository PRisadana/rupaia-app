<div id="layoutSidenav_nav">
    <nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">
        <div class="sb-sidenav-menu">
            <div class="nav">

                <div class="sb-sidenav-menu-heading">General Information</div>

                <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"
                    href="{{ route('admin.dashboard') }}">
                    <div class="sb-nav-link-icon d-inline-flex align-items-center">
                        <i class="fi fi-rr-chart-histogram mt-1"></i>
                    </div>
                    Dashboard
                </a>

                <div class="sb-sidenav-menu-heading">Management</div>

                <a class="nav-link {{ request()->routeIs('admin.user.*') ? 'active' : '' }}"
                    href="{{ route('admin.user.index') }}">
                    <div class="sb-nav-link-icon d-inline-flex align-items-center">
                        <i class="fi fi-rr-user mt-1"></i>
                    </div>
                    Users
                </a>

                <a class="nav-link {{ request()->routeIs('admin.content.*') ? 'active' : '' }}"
                    href="{{ route('admin.content.index') }}">
                    <div class="sb-nav-link-icon d-inline-flex align-items-center">
                        <i class="fi fi-rr-picture mt-1"></i>
                    </div>
                    Contents
                </a>

                <a class="nav-link {{ request()->routeIs('admin.folder.*') ? 'active' : '' }}"
                    href="{{ route('admin.folder.index') }}">
                    <div class="sb-nav-link-icon d-inline-flex align-items-center">
                        <i class="fi fi-rr-folder mt-1"></i>
                    </div>
                    Folders
                </a>

                <a class="nav-link {{ request()->routeIs('admin.showcase.*') ? 'active' : '' }}"
                    href="{{ route('admin.showcase.index') }}">
                    <div class="sb-nav-link-icon d-inline-flex align-items-center">
                        <i class="fi fi-rr-file-image mt-1"></i>
                    </div>
                    Showcases
                </a>

                <div class="sb-sidenav-menu-heading">Moderation</div>

                <a class="nav-link {{ request()->routeIs('admin.report.index') ? 'active' : '' }}"
                    href="{{ route('admin.report.index') }}">
                    <div class="sb-nav-link-icon d-inline-flex align-items-center">
                        <i class="fi fi-rr-flag mt-1"></i>
                    </div>
                    Content Reports
                </a>

                <a class="nav-link {{ request()->routeIs('admin.report.showcase.*') ? 'active' : '' }}"
                    href="{{ route('admin.report.showcase.index') }}">
                    <div class="sb-nav-link-icon d-inline-flex align-items-center">
                        <i class="fi fi-rr-flag mt-1"></i>
                    </div>
                    Showcase Reports
                </a>

                <div class="sb-sidenav-menu-heading">Transactions</div>

                <a class="nav-link" href="#">
                    <div class="sb-nav-link-icon d-inline-flex align-items-center">
                        <i class="fi fi-rr-assept-document mt-1"></i>
                    </div>
                    KYC Verification
                </a>

                <a class="nav-link" href="#">
                    <div class="sb-nav-link-icon d-inline-flex align-items-center">
                        <i class="fi fi-rr-exchange mt-1"></i>
                    </div>
                    Transaction
                </a>

                <a class="nav-link" href="#">
                    <div class="sb-nav-link-icon d-inline-flex align-items-center">
                        <i class="fi fi-rr-wallet mt-1"></i>
                    </div>
                    Payout
                </a>

                <div class="sb-sidenav-menu-heading">Configurations</div>

                <a class="nav-link {{ request()->routeIs('admin.preset.*') ? 'active' : '' }}"
                    href="{{ route('admin.preset.index') }}">
                    <div class="sb-nav-link-icon d-inline-flex align-items-center">
                        <i class="fi fi-rr-palette mt-1"></i>
                    </div>
                    Presets
                </a>

                <a class="nav-link {{ request()->routeIs('admin.tag.*') ? 'active' : '' }}"
                    href="{{ route('admin.tag.index') }}">
                    <div class="sb-nav-link-icon d-inline-flex align-items-center">
                        <i class="fi fi-rr-tags mt-1"></i>
                    </div>
                    Tags
                </a>

            </div>
        </div>

        <div class="sb-sidenav-footer">
            <div class="dropdown">
                <a class="nav-link dropdown-toggle d-flex align-items-center gap-2 text-white text-decoration-none"
                    id="navbarDropdown" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <img src="{{ Auth::user()->profile_photo_path ? asset('storage/' . Auth::user()->profile_photo_path) : asset('aset/rupaia_logo.png') }}"
                        alt="Profile" class="rounded-circle" width="30" height="30" style="object-fit: cover;">
                    <span class="text-white text-truncate">{{ Auth::user()->name ?? 'Admin' }}</span>
                </a>

                <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2" aria-labelledby="navbarDropdown">
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

                    <li>
                        <hr class="dropdown-divider my-1">
                    </li>

                    <li>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit"
                                class="dropdown-item py-2 d-flex align-items-center gap-2 text-danger fw-semibold">
                                <i class="fi fi-rr-sign-out-alt mt-1"></i>
                                Logout
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
</div>
