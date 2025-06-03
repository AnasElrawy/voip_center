@extends('layouts.customer')

@section('content')

@if (session('success'))
  <div class="alert alert-success text-center">{{ session('success') }}</div>
@endif



<div class="container-fluid" >
  <div class="row h-100">
    {{-- the image --}}
    <div class="col-lg-6 d-none d-lg-flex align-items-center justify-content-center p-0">
      <img src="{{ asset('images/Complete Registration.png') }}" 
           alt="Complete Profile Illustration"
           class="img-fluid"
           style="max-height: 90%;">
    </div>

    {{-- complete data form --}}
    <div class="col-12 col-lg-6 d-flex align-items-center justify-content-center">
      <div class="card shadow-lg border-0 rounded-4 w-75">
        <div class="card-body p-4">
          <h4 class="mb-3 text-center fw-semibold">Complete Your Profile</h4>

          <form method="POST" action="{{ route('customer.complete-registration') }}">
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



            {{-- Username (readonly) --}}
            <div class="mb-3">
              <label for="username" class="form-label">Username</label>
              <input  type="text" id="username" name="username" 
                    value="{{ old('username', $username) }}"
                    class="form-control readonly-disabled" readonly>
            </div>

            @if (email_enabled())
              {{-- Email --}}
              <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" id="email" name="email" 
                      value="{{ old('email', $email) }}"
                      class="form-control @error('email') is-invalid @enderror @if (!empty($email)) readonly-disabled @endif"
                      @if (!empty($email)) readonly @endif>
                @error('email')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            @endif


            {{-- Phone --}}
            <div class="mb-2">
              <label class="form-label">Phone Number</label>
              <input type="text" id="phone" name="phone" value="{{ old('phone') }}"
                     class="form-control @error('phone_full') is-invalid @enderror" placeholder="Phone number" required>
              @error('phone_full')
                <div class="invalid-feedback d-block">{{ $message }}</div>
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
              Save and Continue
            </button>

          </form>
        </div>
      </div>
    </div>
  </div>
</div>

<style>

  .readonly-disabled {
    background-color: #e9ecef;  
    opacity: 1;                 
    pointer-events: none;       
  }

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
