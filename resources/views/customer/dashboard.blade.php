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
</style>

<div class="container py-5">
    <h2 class="mb-4">Welcome, {{ auth('customer')->user()->username }} 👋</h2>

    <div class="dashboard-cards d-flex gap-4">
        <!-- User Info -->
        <div class="dashboard-card">
            <div class="dashboard-card-header">
                <i class="icon-primary bi bi-person-circle"></i> User Information
            </div>
            <p><strong>Email:</strong> {{ auth('customer')->user()->email ?? 'N/A' }}</p>
            <p><strong>Phone:</strong> {{ auth('customer')->user()->phone_number ?? 'N/A' }}</p>
            <p><strong>Timezone:</strong> {{ auth('customer')->user()->timezone ?? 'N/A' }}</p>
        </div>

        <!-- Balance Info -->
        <div class="dashboard-card">
            <div class="dashboard-card-header">
                <i class="icon-success bi bi-wallet2"></i> Balance Overview
            </div>
            <p><strong>Total Balance:</strong> ${{ number_format($balance['total'], 2) }}</p>
            <p><strong>Specific Balance:</strong> ${{ number_format($balance['specific'], 2) }}</p>
        </div>

        <!-- Actions -->
        <div class="dashboard-card">
            <div class="dashboard-card-header">
                <i class="icon-secondary bi bi-gear"></i> Quick Actions
            </div>
            <a href="" class="btn btn-outline-primary mb-2">🔑 Change Password</a>
            <a href="{{ route('logout') }}" class="btn btn-outline-danger">🚪 Logout</a>
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
                                    <td>{{ \Carbon\Carbon::parse($call['datetime'])->format('Y-m-d H:i') }}</td>
                                    <td>{{ $call['number'] }}</td>
                                    <td>{{ $call['duration'] }} mins</td>
                                    <td>${{ number_format($call['cost'], 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
