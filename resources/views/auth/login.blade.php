@extends('layouts.customer')

@section('content')
<div class="container-fluid " >
    <div class="row h-100">
        <div class="col-lg-6 d-none d-lg-flex align-items-center justify-content-center p-0">
            <img src="{{ asset('images/login-DdidSgyI.svg') }}" alt="Login Illustration" class="img-fluid" style="max-height: 90%;">
        </div>

        <div class="col-12 col-lg-6 d-flex align-items-center justify-content-center">
            <div class="card shadow-lg border-0 rounded-4 w-75">
                <div class="card-body p-4">
                    <h4 class="mb-3 text-center fw-semibold">Welcome Back</h4>

                    <!-- @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif -->

                    @if ($errors->has('msg'))
                        <div class="alert alert-danger">{{ $errors->first('msg') }}</div>
                    @endif

                    <form method="POST" action="{{ route('customer.login.submit') }}" novalidate>
                        @csrf

                        {{-- Username --}}
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" id="username" name="username" value="{{ old('username') }}" class="form-control @error('username') is-invalid @enderror" required autofocus>
                            @error('username')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>


                        {{-- Password --}}
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" id="password" name="password" class="form-control @error('password') is-invalid @enderror" required>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Submit --}}
                        <button type="submit" class="btn btn-primary w-100 py-2 fs-6 rounded-pill">
                            Login
                        </button>
                    </form>

                    
                    @if (email_enabled())

                        <div class="mt-3 text-center">
                            <a href="{{ route('customer.forgotPassword.form') }}" class="small">Forgot Password?</a>
                        </div>

                    @endif


                    <div class="mt-3 text-center">
                        <a href="{{ route('customer.register.form') }}" class="small">Don't have an account? Create one</a>
                    </div>


                </div>
            </div>
        </div>
    </div>
</div>
@endsection
