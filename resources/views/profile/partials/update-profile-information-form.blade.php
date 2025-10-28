<section>
    <header>
        <h2 class="card-title mb-3">
            {{ __('Profile Information') }}
        </h2>

        <p class="card-subtitle mb-3 text-muted">
            {{ __("Update your account's profile information and email address.") }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" enctype="multipart/form-data">
        @csrf
        @method('patch')

        <div class="mb-3 text-center">
            <img src="{{ Auth::user()->profile_photo_path ? asset('storage/' . Auth::user()->profile_photo_path) : asset('aset/rupaia_logo.png') }}"
                alt="Profile" class="rounded-circle" width="150" height="150"
                style="object-fit: cover; border: 4px solid #eee;">
        </div>

        <div class="mb-3">
            <label for="name" :value="__('Name')" class="form-label">Name</label>
            <input id="name" name="name" type="text" class="form-control @error('name') is-invalid @enderror"
                value="{{ old('name', $user->name) }}" required autocomplete="name">

            @error('name')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="email" :value="__('Email')" class="form-label">Email</label>
            <input id="email" name="email" type="email" class="form-control @error('name') is-invalid @enderror"
                value="{{ old('email', $user->email) }}" required autocomplete="username">

            @error('email')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
            @enderror

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && !$user->hasVerifiedEmail())
                <div class="alert alert-warning mt-2">
                    {{ __('Your email address is unverified.') }}
                    <button form="send-verification" class="btn btn-link p-0 m-0 align-baseline">
                        {{ __('Click here to re-send the verification email.') }}
                    </button>
                </div>

                @if (session('status') === 'verification-link-sent')
                    <div class="alert alert-success mt-2">
                        {{ __('A new verification link has been sent to your email address.') }}
                    </div>
                @endif
            @endif
        </div>

        <div class="mb-3">
            <label for="bio" class="form-label">{{ __('Bio') }}</label>
            <textarea class="form-control @error('bio') is-invalid @enderror" id="bio" name="bio">{{ old('bio', $user->bio) }}</textarea>

            @error('bio')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="profile_photo_path" class="form-label">{{ __('Profile Photo') }}</label>
            <input id="profile_photo_path" name="profile_photo_path" type="file"
                class="form-control @error('profile_photo_path') is-invalid @enderror">
            @error('profile_photo_path')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="d-flex align-items-center gap-4">
            <button type="submit" class="btn btn-dark">{{ __('Save') }}</button>
            @if (session('status') === 'profile-updated')
                <span class="text-success">{{ __('Saved.') }}</span>
            @endif
        </div>
    </form>
</section>
