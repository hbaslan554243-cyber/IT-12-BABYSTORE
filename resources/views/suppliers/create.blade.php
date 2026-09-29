@extends('layouts.app')
@section('title', 'Add Supplier')

@section('content')
    <h2>Add Supplier</h2>
    <div class="card p-4">
        <form method="POST" action="{{ route('suppliers.store') }}">
            @csrf
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Supplier Name</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Contact Person</label>
                    <input type="text" name="contact_person" class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Phone</label>
                    <input type="text" name="phone" class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Facebook Handle</label>
                    <input type="text" name="facebook_handle" class="form-control" placeholder="e.g. fb.com/supplierpage">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Address</label>
                    <input type="text" name="address" class="form-control">
                </div>
            </div>
            <button class="btn btn-danger mt-3">Save Supplier</button>
        </form>
    </div>
@endsection
