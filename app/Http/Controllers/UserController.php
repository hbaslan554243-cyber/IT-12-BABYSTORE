<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{

    public function index()
    {
        $users = User::orderBy('role')->orderBy('name')->get();

        return view('users.index', compact('users'));
    }

    public function create()
    {
        return view('users.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:150',
            'username' => 'required|string|max:50|unique:users,username',
            'email' => 'required|email|unique:users,email',
            'role' => 'required|in:owner,staff',
            'password' => 'required|string|min:8|confirmed',
        ]);

        User::create([
            'name' => $data['name'],
            'username' => $data['username'],
            'email' => $data['email'],
            'role' => $data['role'],
            'status' => 'active',
            'password' => Hash::make($data['password']),
        ]);

        return redirect()->route('users.index')->with('status', 'Account created successfully.');
    }

    public function toggleStatus(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->withErrors(['status' => 'You cannot deactivate your own account.']);
        }

        $user->update(['status' => $user->status === 'active' ? 'inactive' : 'active']);

        return back()->with('status', 'Account status updated.');
    }

    public function resetPassword(Request $request, User $user)
    {
        $request->validate(['new_password' => 'required|string|min:8']);

        $user->update(['password' => Hash::make($request->new_password)]);

        return back()->with('status', "Password for {$user->name} has been reset.");
    }
}
