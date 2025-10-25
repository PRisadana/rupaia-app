<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
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

        // validasi manual di sini karena ProfileUpdateRequest tidak tahu ttg field baru kita
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
                Storage::delete($request->user()->profile_photo_path);
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
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
