<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class BankAccountController extends Controller
{

    public function create()
    {
        return view(
            'bank-accounts.create'
        );
    }

    public function store(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'bank_name' => [
                'required',
                'string',
                'max:100',
            ],

            'account_name' => [
                'required',
                'string',
                'max:255',
            ],

            'account_number' => [
                'required',
                'string',
                'regex:/^[0-9]+$/',
                'min:6',
                'max:30',
            ],
        ], [
            'bank_name.required'
            => 'Bank name is required.',

            'account_name.required'
            => 'Account holder name is required.',

            'account_number.required'
            => 'Account number is required.',

            'account_number.regex'
            => 'Account number may only contain numbers.',

            'account_number.min'
            => 'Account number is too short.',

            'account_number.max'
            => 'Account number may not exceed 30 digits.',
        ]);

        $duplicateExists = $user->bankAccounts()
            ->where('bank_name', $validated['bank_name'])
            ->where('account_number', $validated['account_number'])
            ->exists();

        if ($duplicateExists) {
            return back()
                ->withInput()
                ->withErrors([
                    'account_number'
                    => 'This bank account has already been added.',
                ]);
        }

        $hasBankAccount = $user->bankAccounts()
            ->exists();

        $user->bankAccounts()->create([
            'bank_name' => $validated['bank_name'],
            'account_name' => $validated['account_name'],
            'account_number' => $validated['account_number'],
            'is_default' => ! $hasBankAccount,
        ]);

        return redirect()
            ->route('profile.edit')
            ->with(
                'success',
                'Bank account added successfully.'
            );
    }

    public function edit(Request $request, BankAccount $bankAccount)
    {
        if ($bankAccount->seller_id !== $request->user()->id) {
            abort(403);
        }

        return view(
            'bank-accounts.edit',
            compact('bankAccount')
        );
    }

    public function update(Request $request, BankAccount $bankAccount)
    {
        $user = $request->user();

        if ($bankAccount->seller_id !== $user->id) {
            abort(403);
        }

        $validated = $request->validate([
            'bank_name' => [
                'required',
                'string',
                'max:100',
            ],

            'account_name' => [
                'required',
                'string',
                'max:255',
            ],

            'account_number' => [
                'required',
                'string',
                'regex:/^[0-9]+$/',
                'min:6',
                'max:30',
            ],
        ]);

        $duplicateExists = $user->bankAccounts()
            ->where('bank_name', $validated['bank_name'])
            ->where('account_number', $validated['account_number'])
            ->where('id', '!=', $bankAccount->id)
            ->exists();

        if ($duplicateExists) {
            return back()
                ->withInput()
                ->withErrors([
                    'account_number'
                    => 'This bank account has already been added.',
                ]);
        }

        $bankAccount->update([
            'bank_name' => $validated['bank_name'],
            'account_name' => $validated['account_name'],
            'account_number' => $validated['account_number'],
        ]);

        return redirect()
            ->route('profile.edit')
            ->with(
                'success',
                'Bank account updated successfully.'
            );
    }

    public function setDefault(Request $request, BankAccount $bankAccount)
    {
        $user = $request->user();

        if ($bankAccount->seller_id !== $user->id) {
            abort(403);
        }

        DB::transaction(function () use (
            $user,
            $bankAccount
        ) {
            $user->bankAccounts()
                ->where('is_default', true)
                ->update([
                    'is_default' => false,
                ]);
            $bankAccount->update([
                'is_default' => true,
            ]);
        });

        return redirect()
            ->route('profile.edit')
            ->with(
                'success',
                'Default bank account updated successfully.'
            );
    }

    public function destroy(Request $request, BankAccount $bankAccount)
    {
        $user = $request->user();

        if ($bankAccount->seller_id !== $user->id) {
            abort(403);
        }

        DB::transaction(function () use (
            $user,
            $bankAccount
        ) {
            $wasDefault = $bankAccount->is_default;

            $bankAccount->delete();

            if ($wasDefault) {
                $nextAccount = $user->bankAccounts()
                    ->oldest('id')
                    ->first();
                if ($nextAccount) {
                    $nextAccount->update([
                        'is_default' => true,
                    ]);
                }
            }
        });

        return redirect()
            ->route('profile.edit')
            ->with(
                'success',
                'Bank account deleted successfully.'
            );
    }
}
