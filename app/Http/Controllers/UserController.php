<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(): View
    {
        $users = User::withCount([
            'tasks as open_count' => fn ($q) => $q->where('status', '!=', 'done'),
        ])->orderBy('name')->paginate(15);

        return view('admin.users', compact('users'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'      => ['required', 'string', 'max:120'],
            'email'     => ['required', 'email', 'max:190', 'unique:users,email'],
            'job_title' => ['nullable', 'string', 'max:120'],
            'role'      => ['required', Rule::in(array_keys(User::ROLES))],
            'password'  => ['required', Password::min(8)],
        ]);

        User::create($data);

        return back()->with('status', $data['name'].' was added.');
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'role' => ['required', Rule::in(array_keys(User::ROLES))],
        ]);

        if ($user->is($request->user())) {
            return back()->withErrors(['role' => 'You cannot change your own role.']);
        }

        $user->update($data);

        return back()->with('status', $user->name.' is now '.$user->roleLabel().'.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($user->is($request->user())) {
            return back()->withErrors(['role' => 'You cannot delete your own account.']);
        }

        $user->delete();

        return back()->with('status', $user->name.' was removed.');
    }
}
