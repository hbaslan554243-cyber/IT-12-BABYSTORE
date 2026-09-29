@extends('layouts.app')
@section('title', 'Suppliers')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Suppliers</h2>
        @if(auth()->user()->isOwner())
            <a href="{{ route('suppliers.create') }}" class="btn btn-danger">+ Add Supplier</a>
        @endif
    </div>

    <table class="table table-bordered bg-white">
        <thead><tr><th>Name</th><th>Contact Person</th><th>Phone</th><th>Facebook</th><th>Address</th></tr></thead>
        <tbody>
        @forelse($suppliers as $s)
            <tr>
                <td>{{ $s->name }}</td>
                <td>{{ $s->contact_person }}</td>
                <td>{{ $s->phone }}</td>
                <td>{{ $s->facebook_handle }}</td>
                <td>{{ $s->address }}</td>
            </tr>
        @empty
            <tr><td colspan="5" class="text-center text-muted">No suppliers added yet.</td></tr>
        @endforelse
        </tbody>
    </table>
    {{ $suppliers->links() }}
@endsection
