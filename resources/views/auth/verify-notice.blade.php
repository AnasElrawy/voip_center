@extends('layouts.customer')

@section('content')

<div class="container-fluid vh-100">
  <div class="row h-100">

    {{-- the image --}}
    <div class="col-lg-6 d-none d-lg-flex align-items-center justify-content-center p-0">
      <img src="{{ asset('images/email-checker.png') }}" 
           alt="Verify Email Illustration" 
           class="img-fluid" 
           style="max-height: 90%;">
    </div>

    {{-- message --}}
    <div class="col-12 col-lg-6 d-flex align-items-center justify-content-center">
      <div class="card shadow-lg border-0 rounded-4 w-75">
        <div class="card-body p-4 text-center">

          <h4 class="mb-3 fw-semibold">Account Created Successfully!</h4>
          <p class="mb-4">
            We have sent a verification link to your email address.<br>
            Please check your inbox and confirm your email to activate your account.
          </p>

          {{-- Go to login --}}
          <a href="{{ route('customer.login') }}" 
             class="btn btn-primary w-100 py-2 fs-6 rounded-pill mb-3">
            Go to Login
          </a>

          {{-- Resend verification --}}
          <p class="mt-2">
            Didn't receive the email? 
            <a href="{{ route('customer.verify.resend') }}" class="text-decoration-underline">
              Resend Verification Email
            </a>
          </p>

        </div>
      </div>
    </div>

  </div>
</div>

@endsection
