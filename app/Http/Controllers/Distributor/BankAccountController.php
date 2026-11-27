<?php

namespace App\Http\Controllers\Distributor;

use App\Http\Controllers\Controller;
use App\Models\DistributorBankAccount;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Rekening distributor yang tampil saat subdist dan outlet milik distributor membayar
 * (metode Transfer ke rekening distributor). Halaman sendiri: menu Penjualan > Rekening Pembayaran.
 */
class BankAccountController extends Controller
{
    public function index(): View
    {
        $accounts = DistributorBankAccount::where('distributor_id', Auth::id())->orderByDesc('is_active')->orderBy('bank_name')->get();

        return view('buyer.distributor.bank-accounts.index', compact('accounts'));
    }

    public function create(): View
    {
        return view('buyer.distributor.bank-accounts.form', ['account' => new DistributorBankAccount(['is_active' => true])]);
    }

    public function store(Request $request): RedirectResponse
    {
        $account = new DistributorBankAccount($this->validated($request));
        $account->distributor_id = Auth::id();
        $account->save();

        return redirect()->route('distributor.bank-accounts.index')->with('success', 'Rekening ' . $account->bank_name . ' berhasil ditambahkan.');
    }

    public function edit(string $id): View
    {
        return view('buyer.distributor.bank-accounts.form', ['account' => $this->findOwn($id)]);
    }

    public function update(Request $request, string $id): RedirectResponse
    {
        $account = $this->findOwn($id);
        $account->update($this->validated($request, $account));

        return redirect()->route('distributor.bank-accounts.index')->with('success', 'Rekening ' . $account->bank_name . ' berhasil diperbarui.');
    }

    public function destroy(string $id): RedirectResponse
    {
        $account = $this->findOwn($id);
        $account->delete();

        return redirect()->route('distributor.bank-accounts.index')->with('success', 'Rekening ' . $account->bank_name . ' berhasil dihapus.');
    }

    private function findOwn(string $id): DistributorBankAccount
    {
        return DistributorBankAccount::where('distributor_id', Auth::id())->whereKey($id)->firstOrFail();
    }

    private function validated(Request $request, ?DistributorBankAccount $current = null): array
    {
        $request->merge([
            'bank_name' => trim((string) $request->input('bank_name')),
            'account_number' => preg_replace('/[\s\-.]/', '', (string) $request->input('account_number')),
            'account_name' => trim((string) $request->input('account_name')),
        ]);

        $data = $request->validate([
            'bank_name' => ['required', 'string', 'max:100'],
            'account_number' => [
                'required', 'regex:/^[0-9]{5,30}$/',
                Rule::unique('distributor_bank_accounts')
                    ->where('distributor_id', Auth::id())
                    ->where('bank_name', $request->input('bank_name'))
                    ->ignore($current?->getKey()),
            ],
            'account_name' => ['required', 'string', 'max:150'],
            'is_active' => ['nullable', 'boolean'],
        ], [
            'bank_name.required' => 'Nama bank wajib diisi.',
            'account_number.required' => 'Nomor rekening wajib diisi.',
            'account_number.regex' => 'Nomor rekening hanya berisi angka (5-30 digit).',
            'account_number.unique' => 'Rekening ini sudah terdaftar.',
            'account_name.required' => 'Nama pemilik rekening wajib diisi.',
        ]);
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
