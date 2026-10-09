@extends('layouts.app')
@section('title', 'Add Supplier')

@section('content')
    <h2>Add Supplier</h2>
    <div class="card p-4">
        <form method="POST" action="{{ route('suppliers.store') }}">
            @csrf
            @include('suppliers._form')
            <button class="btn btn-danger mt-3">Save Supplier</button>
        </form>
    </div>
@endsection
