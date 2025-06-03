@extends('layouts.customer')

@section('content')
<div class="container py-4" style="max-width: 700px;">
    <div class="card shadow-sm p-3 rounded-4" style="background-color: #ffffff;">
        <h3 class="mb-3 text-dark fw-semibold" style="font-size: 1.6rem;">
            Welcome, {{ auth('customer')->user()->first_name }} {{ auth('customer')->user()->last_name }}
        </h3>

        @if(session('success'))
            <div class="alert alert-success rounded-3 py-2 px-3" style="font-size: 0.9rem;">
                {{ session('success') }}
            </div>
        @endif

        <button 
            class="btn btn-outline-secondary btn-sm mb-4 px-3 py-1 rounded-pill" 
            data-bs-toggle="modal" 
            data-bs-target="#editProfileModal" 
            style="font-weight: 500; font-size: 0.9rem;">
            <i class="bi bi-pencil me-1"></i> Edit Profile
        </button>

        <div class="d-flex flex-column gap-3">
            <div class="d-flex justify-content-between align-items-center border-bottom pb-1">
                <span class="text-muted" style="font-size: 0.85rem;">Username</span>
                <span style="font-size: 1rem; font-weight: 500;">{{ auth('customer')->user()->username }}</span>
            </div>
            <div class="d-flex justify-content-between align-items-center border-bottom pb-1">
                <span class="text-muted" style="font-size: 0.85rem;">Email</span>
                <span style="font-size: 1rem; font-weight: 500;">{{ auth('customer')->user()->email }}</span>
            </div>
            <div class="d-flex justify-content-between align-items-center border-bottom pb-1">
                <span class="text-muted" style="font-size: 0.85rem;">Phone Number</span>
                <span style="font-size: 1rem; font-weight: 500;">{{ auth('customer')->user()->phone_number }}</span>
            </div>
            <div class="d-flex justify-content-between align-items-center border-bottom pb-1">
                <span class="text-muted" style="font-size: 0.85rem;">Timezone</span>
                <span style="font-size: 1rem; font-weight: 500;">{{ auth('customer')->user()->timezone ?? '-' }}</span>
            </div>
            <div class="d-flex justify-content-between align-items-center">
                <span class="text-muted" style="font-size: 0.85rem;">Status</span>
                <span class="badge rounded-pill {{ auth('customer')->user()->is_active ? 'bg-success' : 'bg-secondary' }}" style="font-size: 0.8rem; padding: 0.25em 0.6em;">
                    {{ auth('customer')->user()->is_active ? 'Active' : 'Inactive' }}
                </span>
            </div>
        </div>

        @include('customer.profile.modal')
    </div>
</div>
@endsection
