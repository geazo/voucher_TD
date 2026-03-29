<nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom shadow-sm py-2">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center" href="{{ route('customer.dashboard') }}">
            <img src="{{ asset('logo/logo_tamandayu.jpg') }}" alt="Logo" height="40" class="me-2">
        </a>

        @php
            $user = Auth::guard('customer')->user();
        @endphp

        @if ($user)
            <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse"
                data-bs-target="#customerNav">
                <span class="navbar-toggler-icon"></span>
            </button>
        @endif

        <div class="collapse navbar-collapse" id="customerNav">
            @if ($user)


                <ul class="navbar-nav me-auto mb-2 mb-lg-0 mt-3 mt-lg-0">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('customer.dashboard') ? 'active fw-bold text-success' : '' }}"
                            href="{{ route('customer.dashboard') }}">
                            Dashboard
                        </a>
                    </li>
                </ul>

                <ul class="navbar-nav ms-auto align-items-lg-center">
                    <li class="nav-item dropdown d-none d-lg-block">
                        <a class="nav-link dropdown-toggle fw-semibold text-dark" href="#" role="button"
                            data-bs-toggle="dropdown">
                            {{ $user->nama }}
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2">
                            <li><a class="dropdown-item py-2" href="{{ route('customer.profile') }}"><i class="bi bi-person me-2"></i>Profil Saya</a></li>
                            <li><hr class="dropdown-divider opacity-50"></li>
                            <li>
                                <form method="POST" action="{{ route('customer.logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger fw-bold py-2">
                                        <i class="bi bi-box-arrow-right me-2"></i>Log Out
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </li>

                    <li class="nav-item d-lg-none mt-2 border-top pt-3">
                        <a class="nav-link d-flex align-items-center justify-content-between {{ request()->routeIs('customer.profile') ? 'text-success fw-bold' : 'text-dark fw-bold' }}"
                            href="{{ route('customer.profile') }}">
                            <span>{{ $user->nama }}</span>
                            <i class="bi bi-person"></i>
                        </a>
                    </li>

                    <li class="nav-item d-lg-none mb-3">
                        <form method="POST" action="{{ route('customer.logout') }}" class="m-0 p-0">
                            @csrf
                            <button type="submit"
                                class="nav-link text-danger fw-bold border-0 bg-transparent text-start w-100 py-2">
                                <i class="bi bi-box-arrow-right me-2"></i>Logout
                            </button>
                        </form>
                    </li>

                </ul>
            @endif
        </div>
    </div>
</nav>
