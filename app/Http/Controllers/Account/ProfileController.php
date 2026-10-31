<?php

namespace App\Http\Controllers\Account;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use Illuminate\Support\Facades\Storage;
use App\Services\Account\UserDeletionService;
use App\Http\Controllers\Controller;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        $user = $request->user();

        $bankAccounts = collect();

        if ($user->role === 'seller') {
            $bankAccounts = $user->bankAccounts()
                ->orderByDesc('is_default')
                ->latest()
                ->get();
        }

        return view('profile.edit', compact(
            'user',
            'bankAccounts'
        ));
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        // validasi manual di sini karena ProfileUpdateRequest tidak tahu ttg field baru
        $request->validate([
            'bio' => ['nullable', 'string', 'max:1000'],
            'profile_photo_path' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
        ]);

        //Simpan Bio
        $request->user()->bio = $request->input('bio');

        // Proses upload foto profil jika ada
        if ($request->hasFile('profile_photo_path')) {
            // Hapus foto lama jika ada
            if ($request->user()->profile_photo_path) {
                Storage::disk('public')->delete($request->user()->profile_photo_path);
            }

            // Simpan foto baru dan dapatkan path-nya
            $path = $request->file('profile_photo_path')->store('profile-photos', 'public');
            $request->user()->profile_photo_path = $path;
        }

        // Simpan semua perubahan ke database
        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request, UserDeletionService $userDeletionService): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        $blockReason = $userDeletionService->blockReason($user);

        if ($blockReason) {
            return back()->with('delete_account_error', $blockReason);
        }

        Auth::logout();

        $userDeletionService->delete($user);

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
