@extends('layouts.customer')

@section('content')

@if ($errors->has('msg'))
    <div class="alert alert-danger">{{ $errors->first('msg') }}</div>
@endif

<div class="container-fluid ">
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

            {{-- First and Last Name --}}
            <div class="row mb-3">
              <div class="col-6">
                <label for="first_name" class="form-label">First Name</label>
                <input type="text" id="first_name" name="first_name" value="{{ old('first_name') }}"
                       class="form-control @error('first_name') is-invalid @enderror" required>
                @error('first_name')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>

              <div class="col-6">
                <label for="last_name" class="form-label">Last Name</label>
                <input type="text" id="last_name" name="last_name" value="{{ old('last_name') }}"
                       class="form-control @error('last_name') is-invalid @enderror" required>
                @error('last_name')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div>

            @if (email_enabled())
            
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

            @endif


            {{-- Username --}}
            <div class="mb-2">
              <label for="username" class="form-label">Username</label>
              <input type="text" id="username" name="username" value="{{ old('username') }}"
                     class="form-control @error('username') is-invalid @enderror" required>
              @error('username')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            {{-- Phone --}}
            <div class="mb-2">
              <label class="form-label">Phone Number</label>
              <input type="text" id="phone" name="phone" value="{{ old('phone') }}"
                     class="form-control @error('phone_full') is-invalid @enderror" placeholder="Phone number" required>
              @error('phone_full')
                <div class="invalid-feedback d-block">{{ $message }}</div>
              @enderror
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

            {{-- timezone --}}
            <input type="hidden" name="timezone" id="timezone" />

            {{-- CountryData --}}
            <input type="hidden" name="CountryData" id="CountryData" />

            {{-- ip_address --}}

            <input type="hidden" name="ip_address"  id="ip_address"/>


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


<style>
  .iti { width: 100%; }
</style>
<script type="module">

const input = document.querySelector("#phone");
const iti = window.intlTelInput(input, {
  initialCountry: "auto",
  separateDialCode	:true,
  autoPlaceholder : 'aggressive',
  strictMode: true,
  excludeCountries : ["il"],
  hiddenInput: (telInputName) => ({
    phone: "phone_full",
    country: "country_code"   
  }),
  geoIpLookup: callback => {
    fetch("/get-ip-info")
      .then(res => res.json())
      .then(data => {
        // console.log(data);  
        document.querySelector('input[name="ip_address"]').value = data.ip;
        
        callback(data.country_code);
        updateCountryData();        

      })
      .catch(() => callback("eg"));
  },
  loadUtils: () => import("https://cdn.jsdelivr.net/npm/intl-tel-input@25.3.1/build/js/utils.js"),
});

document.querySelector('input[name="timezone"]').value = Intl.DateTimeFormat().resolvedOptions().timeZone;


input.addEventListener("countrychange", () => {
  
  updateCountryData();
});

function updateCountryData() {
  const countryData = iti.getSelectedCountryData();
  console.log("Country Changed:", countryData);
  document.querySelector('input[name="CountryData"]').value = JSON.stringify(countryData);
}
</script>

@endsection
