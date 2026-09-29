@extends('layouts.app')
@section('title', 'Add Product')

@section('content')
    <h2>Add Product</h2>
    <div class="card p-4">
        <form method="POST" action="{{ route('products.store') }}">
            @csrf
            @include('products._form')
            <button class="btn btn-danger">Save Product</button>
        </form>
    </div>
@endsection
