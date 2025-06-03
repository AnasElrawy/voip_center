@extends('layouts.customer')

@section('content')

<!-- Fullscreen container -->
<div class="container-fluid " >
  <div class="row h-100">

    <!-- Left side: Illustration Image -->
    <div class="col-lg-6 d-none d-lg-flex align-items-center justify-content-center p-0 bg-light">
      <img src="{{ asset('images/forgot password.png') }}" 
           alt="Reset Password Illustration" 
           class="img-fluid" 
           style="max-height: 90%;">
    </div>

    <!-- Right side: Forgot Password Form -->
    <div class="col-12 col-lg-6 d-flex align-items-center justify-content-center">
      <div class="card shadow-lg border-0 rounded-4 w-75">
        <div class="card-body p-4 text-center">

          <h4 class="mb-3 fw-semibold">Forgot Password</h4>
          
          <!-- Success message after email sent -->
          @if (session('status'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
              {{ session('status') }}
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
          @endif

          <p class="mb-4">
            Enter your email address and we'll send you a link to reset your password.
          </p>

          <!-- Email input form -->
          <form method="POST" action="{{ route('customer.forgotPassword.send') }}">
            @csrf
            <div class="mb-3">
              <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" 
                     placeholder="Enter your email" value="{{ old('email') }}" required>
              @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <button type="submit" class="btn btn-primary w-100 py-2 fs-6 rounded-pill mb-3">
              Send Reset Link
            </button>
          </form>

          <!-- Back to login link -->
          <p class="mt-2">
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