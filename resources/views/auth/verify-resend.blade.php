@extends('layouts.customer')

@section('content')

<div class="container-fluid vh-100">
  <div class="row h-100">

  @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif

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

          <h4 class="mb-3 fw-semibold">Resend Email Verification</h4>
          
          {{-- Check if there's any message --}}
          @if(session('message'))
            <div class="alert alert-info">
                <p class="mb-4">
                    <!-- {{ session('message') }}  -->
                    The verification link has expired.<br>
                    Please enter your email address below to receive a new verification link.
                </p>
            </div>
          @else
            <p class="mb-4">
              Please enter your email address below to receive a new verification link.
            </p>
          @endif

          {{-- Form to enter email --}}
          <form action="{{ route('customer.verify.resend') }}" method="POST">
            @csrf
            <div class="mb-3">
              <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" 
                     placeholder="Enter your email" value="{{ old('email') }}" required>
              @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <button type="submit" class="btn btn-primary w-100 py-2 fs-6 rounded-pill mb-3">
              Resend Verification Email
            </button>
          </form>

          {{-- Back to login --}}
          <p class="mt-2">
            Already verified? 
            <a href="{{ route('customer.login') }}" class="text-decoration-underline">
              Go to Login
            </a>
          </p>

        </div>
      </div>
    </div>

  </div>
</div>

@endsection
