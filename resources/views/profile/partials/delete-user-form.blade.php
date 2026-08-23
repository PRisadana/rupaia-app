<section>
    <header>
        <h2 class="card-title mb-3 text-danger">
            {{ __('Delete Account') }}
        </h2>

        <p class="card-subtitle mb-3 text-muted">
            {{ __('Once your account is deleted, all of its resources and data will be permanently deleted.') }}
        </p>
    </header>

    <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteAccountModal">
        {{ __('Delete Account') }}
    </button>

    <div class="modal fade" id="deleteAccountModal" tabindex="-1" aria-labelledby="deleteAccountModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">

                <form method="post" action="{{ route('profile.destroy') }}" class="p-0 m-0">
                    @csrf
                    @method('delete')

                    <div class="modal-body">
                        <p>
                            {{ __('Are you sure you want to delete your account? Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm you would like to permanently delete your account.') }}
                        </p>

                        <div class="mb-3">
                            <label for="delete_password" class="form-label">{{ __('Password') }}</label>
                            <input id="delete_password" name="password" type="password"
                                class="form-control @error('password', 'deleteUser') is-invalid @enderror"
                                autocomplete="current-password" placeholder="{{ __('Password') }}" required>

                            @error('password', 'deleteUser')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                {{ __('Cancel') }}
                            </button>
                            <button type="submit" class="btn btn-danger">
                                {{ __('Delete Account') }}
                            </button>
                        </div>
                </form>
            </div>
</section>
