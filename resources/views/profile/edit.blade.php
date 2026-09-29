@extends('layouts.app')
@section('title', 'My Profile')

@section('content')
    <div class="row g-4">
        <div class="col-md-6">
            <div class="card p-4">
                <h5 class="fw-bold mb-3">Profile Information</h5>
                <form method="POST" action="{{ route('profile.update') }}">
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                        <label class="form-label">Full Name</label>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" value="{{ $user->username }}" class="form-control" disabled>
                        <div class="form-text">Contact the owner to change your username.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Role</label>
                        <input type="text" value="{{ ucfirst($user->role) }}" class="form-control" disabled>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Account Status</label><br>
                        @if($user->status === 'active')
                            <span class="badge bg-success">Active</span>
                        @else
                            <span class="badge bg-secondary">Inactive</span>
                        @endif
                    </div>
                    <button class="btn btn-brand">Save Changes</button>
                </form>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card p-4">
                <h5 class="fw-bold mb-3">Change Password</h5>
                <form method="POST" action="{{ route('profile.password') }}">
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                        <label class="form-label">Current Password</label>
                        <input type="password" name="current_password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">New Password</label>
                        <input type="password" name="new_password" class="form-control" minlength="8" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Confirm New Password</label>
                        <input type="password" name="new_password_confirmation" class="form-control" minlength="8" required>
                    </div>
                    <button class="btn btn-brand">Update Password</button>
                </form>
            </div>
        </div>
    </div>
@endsection
