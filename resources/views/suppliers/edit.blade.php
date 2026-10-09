@extends('layouts.app')
@section('title', 'Edit Supplier')

@section('content')
    <h2>Edit Supplier</h2>
    <div class="card p-4">
        <form method="POST" action="{{ route('suppliers.update', $supplier) }}">
            @csrf
            @method('PUT')
            @include('suppliers._form')
            <button class="btn btn-danger mt-3">Update Supplier</button>
        </form>
    </div>
@endsection
