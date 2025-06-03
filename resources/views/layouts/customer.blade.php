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

  footer {
    /* position: fixed; */
    /* bottom: 0; */
      display: block;
    margin-top:10px
    left: 0;
    width: 100%;
    /* z-index: 999; */
    /* background-color: white; */
  }


  </style>

</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark" style="background-color: {{ $color }};">
  <div class="container d-flex justify-content-between align-items-center">

    <!-- الشعار واسم الموقع -->
    <div class="d-flex align-items-center">
      <img src="{{ logo_image() }}" alt="Logo" height="40" class="me-2">
      <a class="navbar-brand" href="">{{ web_name() }}</a>
    </div>

    <!-- زر التبديل فقط للعرض على الشاشات الصغيرة -->
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
      <span class="navbar-toggler-icon"></span>
    </button>

    <!-- روابط التنقل وصورة المستخدم معاً على اليمين -->
    <div class="collapse navbar-collapse justify-content-end align-items-center" id="navMenu">
      <ul class="navbar-nav d-flex align-items-center">
        @auth
          <li class="nav-item"><a class="nav-link" href="{{ route('customer.dashboard') }}">Dashboard</a></li>
          <li class="nav-item"><a class="nav-link" href="#">Recharge</a></li>
          <li class="nav-item"><a class="nav-link" href="{{ route('customer.call.history') }}">Call History</a></li>

          <!-- صورة المستخدم مع قائمة Dropdown -->
          <li class="nav-item dropdown ms-3">
            <a class="nav-link p-0" href="#" id="profileMenu" data-bs-toggle="dropdown" aria-expanded="false">
              <img src="{{ Auth::user()->avatar_url ?? asset('images/default-avatar.png') }}"
                   alt="Avatar"
                   class="rounded-circle"
                   height="36"
                   width="36"
                   style="object-fit: cover; padding: 2px; border: 1px solid rgba(255,255,255,0.3);">
            </a>
            <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="profileMenu" style="min-width: 200px;">
              <li class="dropdown-header text-center py-2">
                <strong>{{ Auth::user()->username }}</strong>
              </li>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item" href="{{ route('customer.profile.show') }}"><i class="bi bi-person me-2"></i> Profile</a></li>
              <li><a class="dropdown-item" href="{{ route('customer.changePassword.form') }}"><i class="bi bi-lock me-2"></i> Change Password</a></li>
              <li><hr class="dropdown-divider"></li>
              <li>
                <form method="POST" action="{{ route('logout') }}">
                  @csrf
                  <button class="dropdown-item">
                    <i class="bi bi-box-arrow-right me-2"></i> Logout
                  </button>
                </form>
              </li>
            </ul>
          </li>
        @else
          <li class="nav-item"><a class="nav-link" href="{{ route('customer.login.form') }}">Login</a></li>
          <li class="nav-item"><a class="nav-link" href="{{ route('customer.register.form') }}">Sign Up</a></li>
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
  <main class="container my-4 flex-grow-1 d-flex  justify-content-center">
    @yield('content')
  </main>

  <!-- Footer -->
  <footer class="bg-white text-center py-3 border-top">
    <div class="container">
      <small>&copy; {{ date('Y') }} {{ web_name() }}. All rights reserved.</small>
      <br>
      <small>V {{ config('my_app_settings.version') }}</small>
    </div>
  </footer>

  <!-- Bootstrap JS Bundle -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  @stack('scripts')
</body>
</html>
