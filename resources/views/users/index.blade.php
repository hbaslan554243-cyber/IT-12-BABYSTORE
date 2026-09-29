@extends('layouts.app')
@section('title', 'Manage Users')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Manage Users / Accounts</h2>
        <a href="{{ route('users.create') }}" class="btn btn-brand">+ Add Account</a>
    </div>

    <table class="table table-bordered bg-white">
        <thead><tr><th>Name</th><th>Username</th><th>Email</th><th>Role</th><th>Status</th><th></th></tr></thead>
        <tbody>
        @foreach($users as $u)
            <tr>
                <td>{{ $u->name }}</td>
                <td>{{ $u->username }}</td>
                <td>{{ $u->email }}</td>
                <td><span class="badge bg-secondary text-capitalize">{{ $u->role }}</span></td>
                <td>
                    @if($u->status === 'active')
                        <span class="badge bg-success">Active</span>
                    @else
                        <span class="badge bg-secondary">Inactive</span>
                    @endif
                </td>
                <td class="d-flex gap-2">
                    <form action="{{ route('users.toggle-status', $u) }}" method="POST">
                        @csrf
                        <button class="btn btn-sm btn-outline-secondary" {{ $u->id === auth()->id() ? 'disabled' : '' }}>
                            {{ $u->status === 'active' ? 'Deactivate' : 'Activate' }}
                        </button>
                    </form>
                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#resetModal{{ $u->id }}">Reset Password</button>
                </td>
            </tr>

            <div class="modal fade" id="resetModal{{ $u->id }}" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content" style="border-radius:16px;">
                        <div class="modal-body p-4">
                            <h5 class="fw-bold mb-3">Reset Password for {{ $u->name }}</h5>
                            <form action="{{ route('users.reset-password', $u) }}" method="POST">
                                @csrf
                                <input type="password" name="new_password" class="form-control mb-3" placeholder="New password (min. 8 characters)" minlength="8" required>
                                <button class="btn btn-brand w-100">Reset Password</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
        </tbody>
    </table>
@endsection
