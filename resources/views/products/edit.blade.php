@extends('layouts.app')
@section('title', 'Edit Product')

@section('content')
    <h2>Edit Product</h2>
    <div class="card p-4">
        <form method="POST" action="{{ route('products.update', $product) }}">
            @csrf
            @method('PUT')
            @include('products._form')
            <button class="btn btn-danger">Update Product</button>
        </form>
    </div>
@endsection
