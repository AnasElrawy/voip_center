<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>@yield('title', 'Customer Portal')</title>
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Custom CSS -->
  <!-- <link href="{{ asset('css/customer.css') }}" rel="stylesheet"> -->

  <!-- for intl-tel -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/intl-tel-input@25.3.1/build/css/intlTelInput.css">
  <script src="https://cdn.jsdelivr.net/npm/intl-tel-input@25.3.1/build/js/intlTelInput.min.js"></script>

  <!-- for axios -->
  <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

  <!-- Bootstrap Icon -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">




  @php
    $color = layout_color();
  @endphp

  <style>
    
  html, body {
    height: 100%;
    margin: 0;
  }

  body {
    display: flex;
    flex-direction: column;
  }

  main {
    flex: 1;
  }
    

  .btn {
    background-color: {{ $color }} !important;
    border-color: {{ $color }} !important;
    color: white !important;
  }

  .btn:hover,
  .btn:focus {
    filter: brightness(90%);
  }


  </style>

</head>
<body class="bg-light">

  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg navbar-dark "  style="background-color: {{ $color }};">
    <div class="container">
      <img src="{{ logo_image() }}" alt="Logo" height="40" style='margin-right: 10px;'>
      <a class="navbar-brand" href="">MyVoIP</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navMenu">
        <ul class="navbar-nav ms-auto">
          @auth
            <li class="nav-item"><a class="nav-link" href="{{ route('customer.dashboard') }}">Dashboard</a></li>
            <li class="nav-item"><a class="nav-link" href="">Recharge</a></li>
            <li class="nav-item"><a class="nav-link" href="{{ route('customer.call.history') }}">Call History</a></li>
            <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle" href="#" id="profileMenu" data-bs-toggle="dropdown">
                {{ Auth::user()->username }}
              </a>
              <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="profileMenu">
                <li><a class="dropdown-item" href="{{ route('customer.changePassword.form') }}">Change Password</a></li>
                <li>
                  <form method="POST" action="{{ route ('logout')}}">
                    @csrf
                    <button class="dropdown-item">Logout</button>
                  </form>
                </li>
              </ul>
            </li>
          @else
            <li class="nav-item"><a class="nav-link" href="{{ route ('customer.login.form')}}">Login</a></li>
            <li class="nav-item"><a class="nav-link" href="{{ route ('customer.register.form')}}">Sign Up</a></li>
          @endauth
        </ul>
      </div>
    </div>
  </nav>


@php
  $alertTypes = ['success' => 'success', 'error' => 'danger', 'info' => 'info', 'warning' => 'warning'];
@endphp

<div class="container mt-4">
  @foreach ($alertTypes as $msg => $alertClass)
    @if(session($msg))
      <div class="alert alert-{{ $alertClass }} alert-dismissible fade show" role="alert">
        {{ session($msg) }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    @endif
  @endforeach
</div>


  <!-- Page Content -->
  <main class="container my-4">
    @yield('content')
  </main>

  <!-- Footer -->
  <footer class="bg-white text-center py-3 border-top">
    <div class="container">
      <small>&copy; {{ date('Y') }} MyVoIP. All rights reserved.</small>
      <br>
      <small>V {{ config('my_app_settings.version') }}</small>
    </div>
  </footer>

  <!-- Bootstrap JS Bundle -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  @stack('scripts')
</body>
</html>
