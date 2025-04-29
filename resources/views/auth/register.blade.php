@extends('layouts.customer')

@section('content')

@if ($errors->has('msg'))
    <div class="alert alert-danger">{{ $errors->first('msg') }}</div>
@endif

<div class="container-fluid vh-100">
  <div class="row h-100">
    {{-- the image --}}
    <div class="col-lg-6 d-none d-lg-flex align-items-center justify-content-center p-0">
      <img src="{{ asset('images/sign-up.png') }}"
           alt="Signup Illustration"
           class="img-fluid"
           style="max-height: 90%;">
    </div>

    {{-- register form --}}
    <div class="col-12 col-lg-6 d-flex align-items-center justify-content-center">
      <div class="card shadow-lg border-0 rounded-4 w-75">
        <div class="card-body p-4">
          <h4 class="mb-3 text-center fw-semibold">Create Your Account</h4>
          <form method="POST" action="{{ route('customer.register') }}" novalidate>
            @csrf

            {{-- Email --}}
            <div class="mb-2">
              <label for="email" class="form-label">Email</label>
              <input type="email" id="email" name="email" value="{{ old('email') }}"
                class="form-control @error('email') is-invalid @enderror" required>
              @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
              @else
                <div class="form-text">We'll never share your email.</div>
              @enderror
            </div>

            {{-- Username --}}
            <div class="mb-2">
              <label for="username" class="form-label">Username</label>
              <input type="text" id="username" name="username" value="{{ old('username') }}"
                class="form-control @error('username') is-invalid @enderror" required>
              @error('username')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            {{-- Phone + Country Code --}}
            <div class="mb-2">
              <label class="form-label">Phone Number</label>
              <!-- <div class="row g-2"> -->
                <!-- <div class="col-4">
                  <input type="number"
                         id="country_code"
                         name="country_code"
                         value="{{ old('country_code') }}"
                         class="form-control @error('country_code') is-invalid @enderror"
                         placeholder="Code" min="0" max="999"
                         required>
                  @error('country_code')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                  @enderror
                </div> -->
                <!-- <div class="col-8"> -->
                  <input type="text"
                         id="phone"
                         name="phone"
                         value="{{ old('phone') }}"
                         class="form-control @error('phone') is-invalid @enderror"
                         placeholder="Phone number"
                         required>
                  @error('phone')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                  @enderror
                <!-- </div> -->
              <!-- </div> -->
              <div class="form-text">Enter in international format, e.g. +441234567890</div>
            </div>

            {{-- Password --}}
            <div class="mb-2">
              <label for="customerpassword" class="form-label">Password</label>
              <input type="password" id="customerpassword" name="customerpassword"
                class="form-control @error('customerpassword') is-invalid @enderror" required>
              @error('customerpassword')
                <div class="invalid-feedback">{{ $message }}</div>
              @else
                <div class="form-text">4–39 chars; letters, numbers, -, _, @, .</div>
              @enderror
            </div>

            {{-- Confirm Password --}}
            <div class="mb-3">
              <label for="customerpassword_confirmation" class="form-label">Confirm Password</label>
              <input type="password" id="customerpassword_confirmation" name="customerpassword_confirmation"
                class="form-control @error('customerpassword_confirmation') is-invalid @enderror" required>
              @error('customerpassword_confirmation')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            {{-- Submit --}}
            <button type="submit" class="btn btn-primary w-100 py-2 fs-6 rounded-pill">
              Create Account
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>


@endsection
