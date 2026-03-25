<nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom shadow-sm">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center" href="/">
            <img src="{{ asset('logo/logo_tamandayu.jpg') }}" alt="Logo" height="40" class="me-2">
            <span class="fw-bold text-success">
                {{ config('', '') }}

                @guest
                    <span class="ms-2 text-muted fs-6">| Operator Login</span>
                @endguest
            </span>
        </a>

        <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse"
            data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            @auth
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">

                    @if (in_array(Auth::user()->role, ['admin', 'superadmin']))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('dashboard') ? 'active fw-bold text-success' : '' }}"
                                href="{{ route('dashboard') }}">
                                Dashboard
                            </a>
                        </li>
                    @endif

                    @if (Auth::user()->role === 'superadmin')
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle {{ request()->routeIs('admin.transactions') || request()->routeIs('admin.items.*') || request()->routeIs('customers.*') || request()->routeIs('operators.*') || request()->routeIs('superadmin.extensions.*') ? 'active fw-bold' : '' }}"
                                href="#" id="adminDropdown" role="button" data-bs-toggle="dropdown">
                                Administrasi
                            </a>
                            <ul class="dropdown-menu shadow-sm border-0 mt-2" aria-labelledby="adminDropdown">
                                <li><a class="dropdown-item {{ request()->routeIs('admin.transactions') ? 'active bg-success text-white' : '' }}" href="{{ route('admin.transactions') }}">Riwayat Transaksi</a></li>
                                <li><a class="dropdown-item {{ request()->routeIs('admin.items.index') ? 'active bg-success text-white' : '' }}" href="{{ route('admin.items.index') }}">Master Item</a></li>
                                <li><a class="dropdown-item {{ request()->routeIs('customers.index') ? 'active bg-success text-white' : '' }}" href="{{ route('customers.index') }}">Master Customer</a></li>
                                <li><a class="dropdown-item {{ request()->routeIs('superadmin.extensions.index') ? 'active bg-success text-white' : '' }}" href="{{ route('superadmin.extensions.index') }}">Approval Extensi Saldo</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item {{ request()->routeIs('operators.*') ? 'active bg-success text-white' : '' }}" href="{{ route('operators.index') }}"><i class="bi bi-shield-lock me-1"></i> Master Operator</a></li>
                            </ul>
                        </li>

                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle {{ request()->routeIs('topup.*') || request()->routeIs('customers.create') || request()->routeIs('scan.*') ? 'active fw-bold' : '' }}"
                                href="#" id="kasirDropdown" role="button" data-bs-toggle="dropdown">
                                Kasir
                            </a>
                            <ul class="dropdown-menu shadow-sm border-0 mt-2" aria-labelledby="kasirDropdown">
                                <li><a class="dropdown-item {{ request()->routeIs('topup.index') ? 'active bg-success text-white' : '' }}" href="{{ route('topup.index') }}">Topup Customer</a></li>
                                <li><a class="dropdown-item {{ request()->routeIs('customers.create') ? 'active bg-success text-white' : '' }}" href="{{ route('customers.create') }}">Registrasi Customer</a></li>
                                <li><a class="dropdown-item {{ request()->routeIs('scan.index') ? 'active bg-success text-white' : '' }}" href="{{ route('scan.index') }}">Payment POS</a></li>
                            </ul>
                        </li>
                    @endif

                    @if (Auth::user()->role === 'admin')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.transactions') ? 'active fw-bold text-success' : '' }}" href="{{ route('admin.transactions') }}">
                                Riwayat Transaksi
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.items.index') ? 'active fw-bold text-success' : '' }}" href="{{ route('admin.items.index') }}">
                                Master Item
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('customers.index') ? 'active fw-bold text-success' : '' }}" href="{{ route('customers.index') }}">
                                Master Customer
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.extensions.create') ? 'active fw-bold text-success' : '' }}" href="{{ route('admin.extensions.create') }}">
                                Request Ekstensi
                            </a>
                        </li>
                    @endif

                    @if (Auth::user()->role === 'kasir')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('topup.index') ? 'active fw-bold text-success' : '' }}" href="{{ route('topup.index') }}">
                                Topup Customer
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('customers.create') ? 'active fw-bold text-success' : '' }}" href="{{ route('customers.create') }}">
                                Registrasi
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('scan.index') ? 'active fw-bold text-success' : '' }}" href="{{ route('scan.index') }}">
                                Payment
                            </a>
                        </li>
                    @endif

                </ul>

                <ul class="navbar-nav ms-auto align-items-lg-center">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle fw-semibold" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            {{ Auth::user()->nama ?? (Auth::user()->name ?? 'User') }}
                            <span class="badge {{ Auth::user()->role === 'superadmin' ? 'bg-danger' : (Auth::user()->role === 'admin' ? 'bg-primary' : 'bg-secondary') }} ms-1">
                                {{ strtoupper(Auth::user()->role ?? 'Customer') }}
                            </span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 mt-2">
                            <li><a class="dropdown-item" href="{{ route('admin.profile') }}">Profile</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger fw-bold">
                                        <i class="bi bi-box-arrow-right me-1"></i> Log Out
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </li>
                </ul>
            @else
                @if (!request()->routeIs('login'))
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item">
                            <a class="btn btn-outline-success fw-bold px-4" href="{{ route('login') }}">Login Portal</a>
                        </li>
                    </ul>
                @endif
            @endauth
        </div>
    </div>
</nav>
