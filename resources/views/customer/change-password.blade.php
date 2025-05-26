@extends('layouts.customer')

@section('content')
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">

            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white border-bottom-0 text-center rounded-top-4">
                    <h5 class="mb-0 text-secondary">Change Password</h5>
                </div>

                <div class="card-body">

                    @if($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <ul class="mb-0">
                                @foreach($errors->all() as $e)
                                    <li>{{ $e }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    {{-- الفورم --}}
                    <form method="POST" action="{{ route('customer.changePassword') }}">
                        @csrf

                        <div class="mb-3">
                            <label for="old_password" class="form-label text-muted">Current Password</label>
                            <input type="password" name="old_password" id="old_password" class="form-control rounded-3 border-light bg-light" required>
                        </div>

                        <div class="mb-4">
                            <label for="new_password" class="form-label text-muted">New Password</label>
                            <input type="password" name="new_password" id="new_password" class="form-control rounded-3 border-light bg-light" required>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary rounded-pill">Change Password</button>
                        </div>
                    </form>

                </div>
            </div>

        </div>
    </div>
</div>
@endsection
