@extends('layouts.app')

@section('content')
<div class="container text-center py-5">
    <h2>📧 Please Check Your Email</h2>
    <p>We've sent a verification link to your email.</p>
    <p>If you didn't receive the email, <a href="{{ route('customer.resend.verification', ['email' => $customer->email]) }}">click here to resend</a>.</p>
</div>
@endsection
