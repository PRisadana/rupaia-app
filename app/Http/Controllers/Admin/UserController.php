<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Services\UserDeletionService;
use Illuminate\Validation\Rules;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function __construct(
        private UserDeletionService $userDeletionService
    ) {}

    private const ROLES = ['admin', 'buyer', 'seller'];

    public function index(Request $request)
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'role' => ['nullable', Rule::in(self::ROLES)],
        ]);

        $users = User::query()
            ->when($filters['role'] ?? null, fn($query, $role) => $query->where('role', $role))
            ->when($filters['q'] ?? null, fn($query, $keyword) => $query->search($keyword, ['name', 'email']))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.user.index', compact('users'));
    }

    public function createUser()
    {
        return view('admin.user.create');
    }

    public function storeUser(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => 'required|in:admin,buyer,seller',
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($request->password),
            'role' => $validated['role'],
        ]);

        return redirect()->route('admin.user.index')->with('success', 'User created successfully.');
    }

    public function editUser(User $user)
    {
        return view('admin.user.edit', compact('user'));
    }

    public function updateUser(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
            'role' => 'required|in:admin,buyer,seller',
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->role = $validated['role'];

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        return redirect()->route('admin.user.index')->with('success', 'User updated successfully.');
    }

    public function destroyUser(Request $request, User $user)
    {
        if ($user->is($request->user())) {
            return redirect()->route('admin.user.index')
                ->with('error', 'You cannot delete your own account.');
        }

        $blockReason = $this->userDeletionService->blockReason($user);

        if ($blockReason) {
            return redirect()->route('admin.user.index')
                ->with('error', $blockReason);
        }

        $this->userDeletionService->delete($user);

        return redirect()->route('admin.user.index')->with('success', 'User deleted successfully.');
    }
}
