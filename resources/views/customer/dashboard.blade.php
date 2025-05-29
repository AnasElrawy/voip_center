@extends('layouts.customer')


@section('content')
<style>
    .dashboard-card-header {
        font-weight: 700;
        font-size: 1.2rem;
        margin-bottom: 1rem;
        color: #333;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        background: none !important;
        padding: 0;
        border-radius: 0;
    }

    /* أيقونات ملونة هادئة */
    .icon-primary { color: #4a90e2; }
    .icon-success { color: #28a745; }
    .icon-secondary { color: #6c757d; }
    .icon-info { color: #17a2b8; }

    /* باقي التنسيق */
    .dashboard-card {
        flex: 1;
        padding: 1.5rem;
        background: #fff;
        border-radius: 0;
        box-shadow: none;
        position: relative;
    }
/* 
    .dashboard-card:not(:first-child)::before {
        content: "";
        position: absolute;
        left: 0;
        top: 15%;
        bottom: 15%;
        width: 2px;
        background-color: #ddd;
    } */

    .dashboard-card .btn {
        width: 100%;
        margin-bottom: 0.75rem;
        font-weight: 600;
        border-radius: 0;
        box-shadow: none;
    }

    .dashboard-card .btn:last-child {
        margin-bottom: 0;
    }

    .recent-calls {
        margin-top: 3rem;
        background: #fff;
        border-radius: 0;
        box-shadow: none;
        overflow: hidden;
    }

    .recent-calls table {
        border-collapse: separate;
        border-spacing: 0 0.5rem;
    }

    .recent-calls thead tr th {
        border-bottom: none;
        color: #6c757d;
        font-weight: 600;
    }

    .recent-calls tbody tr {
        background: #f8f9fa;
        border-radius: 0;
    }

    .recent-calls tbody tr td {
        vertical-align: middle;
        padding: 0.75rem 1rem;
    }

    .app-download-badge {
        height: 45px;
        width: auto;
        display: inline-block;
    }


</style>

<div class="container py-5">
    <h2 class="mb-4">Welcome, {{ auth('customer')->user()->first_name }}  {{ auth('customer')->user()->last_name }}👋</h2>

    <div class="dashboard-cards d-flex gap-4">
        <!-- User Info -->
        <div class="dashboard-card">
            <div class="dashboard-card-header">
                <i class="icon-primary bi bi-person-circle"></i> User Information
            </div>
            
            @if (email_enabled())
                <p><strong>Email:</strong> {{ auth('customer')->user()->email ?? 'N/A' }}</p>
            @endif

            <p><strong>Phone:</strong> {{ auth('customer')->user()->phone_number ?? 'N/A' }}</p>
            <p><strong>Timezone:</strong> {{ auth('customer')->user()->timezone ?? 'N/A' }}</p>
        </div>

        <!-- Balance Info -->
        <div class="dashboard-card">
            <div class="dashboard-card-header">
                <i class="icon-success bi bi-wallet2"></i> Balance Overview
            </div>
            <p><strong>Total Balance:</strong> {{currency_symbol()}} {{ number_format($balance['total'], 2) }}</p>
        </div>

        <!-- App Access Info -->
        <div class="dashboard-card">
            <div class="dashboard-card-header">
                <i class="icon-secondary bi bi-phone"></i> Mobile App Access
            </div>
            <p><strong>Username:</strong> {{ $user['username'] }}</p>
            <p>
                <strong>Password:</strong>
                <span id="maskedPassword">••••••••</span><br>
                    <button type="button" onclick="togglePassword()" class="btn btn-primary d-block mx-auto " 
                    style="--bs-btn-padding-y: .25rem; --bs-btn-padding-x: .5rem; --bs-btn-font-size: .75rem; width:30%; border-radius: 10px;">
                    Show</button>
            </p>
            <div class="d-flex align-items-center gap-3 mt-2">
                <a href="https://play.google.com/store/apps/details?id=dellmont.YourDialer&hl=en_US" target="_blank">
                    <img src="{{ asset('images/google-play-badge.png') }}" alt="Download on Google Play" class="app-download-badge" >
                </a>

                <a href="https://apps.apple.com/nl/app/yourdialer/id498950671" target="_blank">
                    <img src="{{ asset('images/app-store-badge.svg') }}" alt="Download on App Store" class="app-download-badge">
                </a>
            </div>

        </div>

    </div>

    <!-- Recent Calls -->
    <div class="recent-calls mt-5 shadow-sm">
        <div class="card-header p-3" style="background: none; border-bottom: none; font-weight: 700; color: #333;">
            <i class="icon-info bi bi-telephone"></i> 📞 Recent Calls
        </div>
        <div class="card-body p-0">
            @if(empty($recentCalls))
                <div class="p-3">No recent calls available.</div>
            @else
                <div class="table-responsive">
                    <table class="table table-striped mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Number</th>
                                <th>Duration</th>
                                <th>Cost</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentCalls as $call)
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($call['datetime'])->format('Y-m-d h:i:s a') }}</td>
                                    <td>{{ $call['number'] }}</td>
                                    <td>{{ $call['duration'] }} </td>
                                    <td>{{currency_symbol()}} {{ number_format($call['cost'], 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>

<script>
    function togglePassword() {
        const el = document.getElementById('maskedPassword');
        if (el.innerText.includes('•')) {
            el.innerText = '{{ $password }}'; 
            event.target.innerText = 'Hide';
        } else {
            el.innerText = '••••••••';
            event.target.innerText = 'Show';
        }
    }
</script>

@endsection
