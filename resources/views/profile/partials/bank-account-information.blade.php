<section>

    <header class="d-flex justify-content-between align-items-start mb-4">
        <div>
            <h2 class="card-title mb-2">
                {{ __('Seller Bank Accounts') }}
            </h2>
            <p class="card-subtitle mb-0 text-muted">
                {{ __('Manage bank accounts that will be used for seller payouts.') }}
            </p>
        </div>

        <a href="{{ route('bank-accounts.create') }}" class="btn btn-dark d-inline-flex align-items-center gap-2">
            <i class="fi fi-rr-square-plus mt-1"></i>
            <span>
                {{ __('Add Bank Account') }}
            </span>
        </a>
    </header>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert">
            </button>
        </div>
    @endif

    <div class="table-responsive">
        <table class="table table-striped table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th scope="col">No</th>
                    <th scope="col">Bank</th>
                    <th scope="col">Account Name</th>
                    <th scope="col">Account Number</th>
                    <th scope="col">Status</th>
                    <th scope="col">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($bankAccounts as $account)
                    <tr>
                        <td>
                            {{ $loop->iteration }}
                        </td>
                        <td>
                            {{ $account->bank_name }}
                        </td>
                        <td>
                            {{ $account->account_name }}
                        </td>
                        <td>
                            {{ $account->account_number }}
                        </td>
                        <td>
                            @if ($account->is_default)
                                <span class="badge bg-success">
                                    Default
                                </span>
                            @else
                                <span class="badge bg-secondary">
                                    Non Default
                                </span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('bank-accounts.edit', $account) }}" class="btn btn-sm btn-outline-primary"
                                title="Edit Bank Account">
                                <i class="fi fi-rr-edit"></i>
                            </a>
                            @if (!$account->is_default)
                                <form method="POST" action="{{ route('bank-accounts.default', $account) }}"
                                    class="d-inline">

                                    @csrf
                                    @method('PATCH')

                                    <button type="submit" class="btn btn-sm btn-outline-success" title="Set as Default"
                                        onclick="return confirm('Set this bank account as default?')">
                                        <i class="fi fi-rr-check"></i>
                                    </button>
                                </form>
                            @endif

                            <form method="POST" action="{{ route('bank-accounts.destroy', $account) }}"
                                class="d-inline">

                                @csrf
                                @method('DELETE')

                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete Bank Account"
                                    onclick="return confirm('Are you sure you want to delete this bank account?')">
                                    <i class="fi fi-rr-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>

                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">
                            No bank accounts added yet.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($bankAccounts->isEmpty())
        <div class="alert alert-light border mt-3 mb-0">
            <div class="small text-muted">
                You have not added a bank account yet.
                A bank account will be required when you request a payout.
            </div>
        </div>
    @endif
</section>
