@extends('layouts.customer')

@section('content')
<div class="container">
    <h2>Call History</h2>



    <form method="GET" action="{{ route('customer.call.history') }}" class="mb-4">
        <div class="row">
            <div class="col-md-3">
                <label>Date From:</label>
                <input type="datetime-local" name="date" class="form-control"
                    value="{{ request('date') }}">
            </div>


            <div class="col-md-2">
                <label>Record Count:</label>
                <input type="number" name="recordcount" class="form-control"
                    value="{{ request('recordcount') ?? 10 }}" min="1" max="500">
            </div>

            <div class="col-md-2">
                <label>Direction:</label>
                <select name="direction" class="form-control">
                    <option value="backward" {{ request('direction') == 'backward' ? 'selected' : '' }}>Backward</option>
                    <option value="forward" {{ request('direction') == 'forward' ? 'selected' : '' }}>Forward</option>
                </select>
            </div>

            <div class="col-md-2">
                <label>Call ID:</label>
                <input type="number" name="callid" class="form-control"
                    value="{{ request('callid') }}">
            </div>

            <div class="col-md-2 mt-4">
                <button class="btn btn-primary w-100">Search</button>
            </div>
        </div>
    </form>

    <p class="text-muted small mb-3" >
        <strong>Note:</strong> You can choose the direction of search:  
        <strong>Forward</strong> (from the selected date **up to now**)  
        or  
        <strong>Backward</strong> (from the selected date **going backward in time**).
    </p>

    @if(empty($calls))
        <div class="alert alert-warning">No call records found.</div>
    @else
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Date & Time</th>
                    <th>Number</th>
                    <th>Duration (sec)</th>
                    <th>Cost</th>
                    <th>Call ID</th>
                </tr>
            </thead>
            <tbody>
                @foreach($calls as $call)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($call['start_time'])->format('Y-m-d h:i:s a') }}</td>
                        <td>{{ $call['destination'] }}</td>
                        <td>{{ $call['duration'] }}</td>
                        <td>{{currency_symbol()}}  {{ $call['charge'] }}</td>
                        <td>{{ $call['callid'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
@endsection
