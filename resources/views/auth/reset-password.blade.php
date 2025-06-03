@extends('layouts.customer')

@section('content')

<div class="container-fluid " >
  <div class="row h-100">

    {{-- Left Side - Illustration --}}
    <div class="col-lg-6 d-none d-lg-flex align-items-center justify-content-center p-0 bg-light">
      <img src="{{ asset('images/reset-password.png') }}" 
           alt="Reset Password Illustration" 
           class="img-fluid" 
           style="max-height: 90%;">
    </div>

    {{-- Right Side - Reset Password Form --}}
    <div class="col-12 col-lg-6 d-flex align-items-center justify-content-center">
      <div class="card shadow-lg border-0 rounded-4 w-75">
        <div class="card-body p-4 text-center">

          <h4 class="mb-3 fw-semibold">Set New Password</h4>
          
          <p class="mb-4">Please enter your email and new password to reset your account.</p>

          @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
              <ul class="mb-0">
                @foreach ($errors->all() as $error)
                  <li>{{ $error }}</li>
                @endforeach
              </ul>
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
          @endif

          {{-- Reset Password Form --}}
          <form method="POST" action="{{ route('customer.resetPassword') }}">
            @csrf

            {{-- Token Hidden Input (for verification) --}}
            <input type="hidden" name="token" value="{{ request('token') }}">
            <!-- <input type="hidden" name="email" value="{{ request('email') }}"> -->

            <!-- <div class="mb-3">
              <input type="email" name="email" class="form-control" placeholder="Your Email Address" required>
            </div> -->

            <div class="mb-3">
              <input type="password" name="password" class="form-control" placeholder="New Password" required>
            </div>

            <div class="mb-3">
              <input type="password" name="password_confirmation" class="form-control" placeholder="Confirm New Password" required>
            </div>

            <button type="submit" class="btn btn-primary w-100 py-2 fs-6 rounded-pill mb-3">
              Reset Password
            </button>
          </form>

          {{-- Back to Login --}}
          <p class="mt-2">
            Remembered your password? 
            <a href="{{ route('customer.login.form') }}" class="text-decoration-underline">
              Back to Login
            </a>
          </p>

        </div>
      </div>
    </div>

  </div>
</div>

@endsection