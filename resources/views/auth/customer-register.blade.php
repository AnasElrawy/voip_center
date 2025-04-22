@extends('layouts.customer')

@section('content')
<div class="container">
  <h2 class="mb-4">Sign Up</h2>
  <form method="POST" action="{{ route('customer.register') }}" novalidate>
    @csrf

    {{-- Email --}}
    <div class="mb-3">
      <label for="email" class="form-label">Email</label>
      <input 
        type="email" 
        id="email" 
        name="email" 
        value="{{ old('email') }}"
        class="form-control @error('email') is-invalid @enderror" 
        required>
      @error('email')
        <div class="invalid-feedback">{{ $message }}</div>
      @else
        <div class="form-text">We'll never share your email.</div>
      @enderror
    </div>

    {{-- Username --}}
    <div class="mb-3">
      <label for="username" class="form-label">Username</label>
      <input 
        type="text" 
        id="username" 
        name="username" 
        value="{{ old('username') }}"
        class="form-control @error('username') is-invalid @enderror" 
        required>
      @error('username')
        <div class="invalid-feedback">{{ $message }}</div>
      @enderror
    </div>

    {{-- Phone --}}
    <div class="mb-3">
      <label for="phone" class="form-label">Phone</label>
      <input 
        type="text" 
        id="phone" 
        name="phone" 
        value="{{ old('phone') }}"
        class="form-control @error('phone') is-invalid @enderror" 
        placeholder="+441234567890"
        required>
      @error('phone')
        <div class="invalid-feedback">{{ $message }}</div>
      @else
        <div class="form-text">Enter in international format, e.g. +441234567890</div>
      @enderror
    </div>

    {{-- Country Code --}}
    <div class="mb-3">
      <label for="country_code" class="form-label">Country Code</label>
      <input 
        type="number" 
        id="country_code" 
        name="country_code" 
        value="{{ old('country_code') }}"
        class="form-control @error('country_code') is-invalid @enderror"
        min="0" max="999"
        required>
      @error('country_code')
        <div class="invalid-feedback">{{ $message }}</div>
      @enderror
    </div>

    {{-- Password --}}
    <div class="mb-3">
      <label for="customerpassword" class="form-label">Password</label>
      <input 
        type="password" 
        id="customerpassword" 
        name="customerpassword"
        class="form-control @error('customerpassword') is-invalid @enderror"
        required>
      @error('customerpassword')
        <div class="invalid-feedback">{{ $message }}</div>
      @else
        <div class="form-text">
          4–39 chars; letters, numbers, -, _, @, .; no “aaaa” or “1234”
        </div>
      @enderror
    </div>

    {{-- Confirm Password --}}
    <div class="mb-3">
      <label for="customerpassword_confirmation" class="form-label">Confirm Password</label>
      <input 
        type="password" 
        id="customerpassword_confirmation" 
        name="customerpassword_confirmation" 
        class="form-control @error('customerpassword_confirmation') is-invalid @enderror"
        required>
      @error('customerpassword_confirmation')
        <div class="invalid-feedback">{{ $message }}</div>
      @enderror
    </div>

    {{-- Submit --}}
    <button type="submit" class="btn btn-primary w-100">Create Account</button>
  </form>
</div>
@endsection
