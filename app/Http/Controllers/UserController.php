<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class UserController extends Controller
{
    private function authorize()
    {
        if (! auth()->user()?->isAdmin()) {
            abort(403, 'Akses ditolak.');
        }
    }

    private function messages(): array
    {
        return [
            'name.required'     => 'Nama wajib diisi.',
            'name.max'          => 'Nama maksimal 255 karakter.',
            'email.required'    => 'Email wajib diisi.',
            'email.email'       => 'Format email tidak valid.',
            'email.max'         => 'Email maksimal 255 karakter.',
            'email.unique'      => 'Email sudah digunakan oleh akun lain.',
            'password.required' => 'Password wajib diisi.',
            'password.min'      => 'Password minimal 8 karakter.',
            'role.required'     => 'Role wajib dipilih.',
            'role.in'           => 'Role yang dipilih tidak valid.',
        ];
    }

    public function index(): View
    {
        $this->authorize();
        $users = User::orderBy('created_at', 'desc')->get();
        return view('v_user_index', compact('users'));
    }

    public function create(): View
    {
        $this->authorize();
        return view('v_user_create');
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize();
        $data = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', Password::defaults()],
            'role'     => ['required', 'in:' . implode(',', array_keys(User::ROLES))],
        ], $this->messages());

        User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
            'role'     => $data['role'],
        ]);

        return redirect()->route('users.index')->with('status', 'Akun berhasil dibuat.');
    }

    public function edit(User $user): View
    {
        $this->authorize();
        return view('v_user_edit', compact('user'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $this->authorize();
        $data = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'role'     => ['required', 'in:' . implode(',', array_keys(User::ROLES))],
            'password' => ['nullable', Password::defaults()],
        ], $this->messages());

        if ($data['password']) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $user->update($data);

        return redirect()->route('users.index')->with('status', 'Akun berhasil diperbarui.');
    }

    public function destroy(User $user): RedirectResponse
    {
        $this->authorize();
        if ($user->id === auth()->id()) {
            return back()->withErrors(['error' => 'Tidak bisa menghapus akun sendiri.']);
        }

        $user->delete();

        return redirect()->route('users.index')->with('status', 'Akun berhasil dihapus.');
    }
}
