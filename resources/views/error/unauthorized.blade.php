@extends('layouts.app')
@section('title', 'Unauthorized Access')

@section('content')
    <div class="card p-5 text-center mx-auto" style="max-width: 480px; margin-top: 60px;">
        <div style="font-size: 42px;">🚫</div>
        <h3 class="fw-bold mt-2">Unauthorized Access</h3>
        <p class="text-muted">Your account role does not have permission to view this page. If you believe this is a mistake, please contact the store owner.</p>
        <a href="{{ route('dashboard') }}" class="btn btn-brand mt-2">Back to Dashboard</a>
    </div>
@endsection
