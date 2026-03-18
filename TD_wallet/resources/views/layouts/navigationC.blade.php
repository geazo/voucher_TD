<nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom shadow-sm">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center" href="{{ route('customer.dashboard') }}">
            <img src="{{ asset('logo/logo_tamandayu.jpg') }}" alt="Logo" height="40" class="me-2">
        </a>

        @if (Auth::guard('customer')->check())
            <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse"
                data-bs-target="#customerNav">
                <span class="navbar-toggler-icon"></span>
            </button>
        @endif

        <div class="collapse navbar-collapse" id="customerNav">
            @if (Auth::guard('customer')->check())
                @php
                    $tierName = Auth::guard('customer')->user()->membership->name ?? 'Customer';
                    $tier = strtolower($tierName);

                    $badgeColor = match ($tier) {
                        'reguler' => 'bg-info text-dark',
                        'silver' => 'bg-secondary text-white',
                        'gold' => 'bg-warning text-dark',
                        'platinum' => 'bg-dark text-white',
                        default => 'bg-light text-dark border border-secondary',
                    };
                    $iconClass = $tierName == 'Customer' ? 'bi-person-fill' : 'bi-star-fill';
                @endphp

                <ul class="navbar-nav me-auto mb-2 mb-lg-0 mt-3 mt-lg-0">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('customer.dashboard') ? 'active fw-bold text-success' : '' }}"
                            href="{{ route('customer.dashboard') }}">
                            Dashboard
                        </a>
                    </li>
                </ul>

                <ul class="navbar-nav ms-auto align-items-lg-center">
                    <li class="nav-item me-3 d-none d-lg-block">
                        <span class="badge {{ $badgeColor }} rounded-pill px-3 py-2 shadow-sm">
                            <i class="bi {{ $iconClass }} me-1"></i> {{ $tierName }}
                        </span>
                    </li>

                    <li class="nav-item dropdown d-none d-lg-block">
                        <a class="nav-link dropdown-toggle fw-semibold" href="#" role="button"
                            data-bs-toggle="dropdown">
                            {{ Auth::guard('customer')->user()->nama }}
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 mt-2">
                            <li><a class="dropdown-item" href="{{ route('customer.profile') }}">Profil Saya</a></li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li>
                                <form method="POST" action="{{ route('customer.logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger fw-bold">Log Out</button>
                                </form>
                            </li>
                        </ul>
                    </li>

                    <li class="nav-item d-lg-none mt-1">
                        <a class="nav-link d-flex align-items-center gap-2 {{ request()->routeIs('customer.profile') ? 'active fw-bold text-success' : 'text-dark fw-bold' }}"
                            href="{{ route('customer.profile') }}">

                            <span>{{ Auth::guard('customer')->user()->nama }}</span>

                            <span class="badge {{ $badgeColor }} rounded-pill px-2 py-1" style="font-size: 0.75rem;">
                                {{ $tierName }}
                            </span>
                        </a>
                    </li>

                    <li class="nav-item d-lg-none mb-3">
                        <form method="POST" action="{{ route('customer.logout') }}" class="m-0 p-0">
                            @csrf
                            <button type="submit"
                                class="nav-link text-danger fw-bold border-0 bg-transparent text-start w-100">
                                Logout
                            </button>
                        </form>
                    </li>

                </ul>
            @endif
        </div>
    </div>
</nav>
