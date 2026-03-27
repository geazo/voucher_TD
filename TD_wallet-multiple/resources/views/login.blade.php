@extends('layouts.app')

@section('content')
<div class="row justify-content-center align-items-center" style="min-height: 75vh;">
    <div class="col-md-6 col-lg-5">
        <div class="card shadow-sm border-0">
            <div class="card-body p-5">

                <div class="text-center mb-4">
                    <img src="{{ asset('logo/logo_tamandayu.jpg') }}" alt="Logo" height="70" class="mb-3 rounded">
                    <h3 class="fw-bold text-success">Portal Login</h3>
                    <p class="text-muted">Silakan masuk menggunakan email dan password Anda.</p>
                </div>

                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('login.submit') }}">
                    @csrf

                    <div class="mb-3">
                        <label for="email" class="form-label fw-semibold">Alamat Email</label>
                        <input type="email" name="email" id="email" value="{{ old('email') }}" required autofocus
                            class="form-control form-control-lg @error('email') is-invalid @enderror"
                            placeholder="example@gmail.com" />
                    </div>

                    <div class="mb-4">
                        <label for="password" class="form-label fw-semibold">Password</label>
                        <input type="password" name="password" id="password" required
                            class="form-control form-control-lg"
                            placeholder="" />
                    </div>

                    <button type="submit" class="btn btn-success btn-lg w-100 fw-bold">
                        MASUK SEKARANG
                    </button>
                </form>

            </div>
        </div>
    </div>
</div>
@endsection
