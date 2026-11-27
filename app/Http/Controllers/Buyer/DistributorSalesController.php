<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * CRUD sales milik distributor yang sedang login. Disimpan di tabel users yang sama dengan Data Sales admin
 * (role sales) dengan distributor_id = distributor pemilik. Distributor hanya melihat dan mengubah sales miliknya.
 *
 * Kode sales dibuat otomatis (MCR + nomor urut, satu urutan dengan sales admin) dan berlaku penuh seperti kode
 * sales perusahaan. Sales distributor bukan akun login: password acak; email opsional (kosong = alamat internal).
 */
class DistributorSalesController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q'));

        $salespeople = $this->ownQuery()
            ->when($search !== '', fn ($q) => $q->where(fn ($w) => $w
                ->where('name', 'like', "%{$search}%")
                ->orWhere('sales_code', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%")))
            ->orderBy('sales_code')
            ->paginate(15)
            ->withQueryString();

        return view('buyer.sales.index', ['salespeople' => $salespeople, 'search' => $search, 'distributor' => Auth::user()]);
    }

    public function create(): View
    {
        return view('buyer.sales.form', ['salesperson' => new User()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $distributorId = $this->distributor()->id;

        $sales = DB::transaction(function () use ($data, $distributorId) {
            $sales = new User();
            $sales->forceFill($data + [
                'role' => User::ROLE_SALES,
                'sales_code' => User::generateSalesCode(),
                'distributor_id' => $distributorId,
                'password' => Hash::make(Str::random(48)),
                'is_active' => true,
            ])->save();

            return $sales;
        });

        return redirect()->route('buyer.sales.index')->with('success', 'Sales ' . $sales->name . ' berhasil ditambahkan dengan kode ' . $sales->sales_code . '.');
    }

    public function edit(string $sale): View
    {
        return view('buyer.sales.form', ['salesperson' => $this->findOwn($sale)]);
    }

    public function update(Request $request, string $sale): RedirectResponse
    {
        $salesperson = $this->findOwn($sale);
        $salesperson->forceFill($this->validated($request, $salesperson))->save();

        return redirect()->route('buyer.sales.index')->with('success', 'Data sales berhasil diperbarui.');
    }

    public function destroy(string $sale): RedirectResponse
    {
        $salesperson = $this->findOwn($sale);
        $salesperson->delete();

        return redirect()->route('buyer.sales.index')->with('success', 'Sales ' . $salesperson->name . ' berhasil dihapus.');
    }

    private function distributor(): User
    {
        $user = Auth::user();
        abort_unless($user && $user->isDistributor(), 403);

        return $user;
    }

    private function ownQuery()
    {
        return User::where('role', User::ROLE_SALES)->where('distributor_id', $this->distributor()->id);
    }

    private function findOwn(string $id): User
    {
        return $this->ownQuery()->whereKey($id)->firstOrFail();
    }

    /**
     * Field form memakai nama sales_name/sales_phone (bukan name/phone) agar browser tidak mengisi otomatis
     * dengan nama dan HP pemilik akun.
     */
    private function validated(Request $request, ?User $current = null): array
    {
        $request->merge(['sales_phone' => preg_replace('/[\s\-()]/', '', (string) $request->input('sales_phone'))]);

        $data = $request->validate([
            'sales_name' => ['required', 'string', 'max:150'],
            'sales_email' => [
                'nullable', 'email', 'max:255',
                Rule::unique('users', 'email')->ignore($current?->getKey()),
            ],
            'sales_phone' => [
                'required', 'string', 'regex:/^\+?[0-9]{8,15}$/',
                Rule::unique('users', 'phone')
                    ->where('role', User::ROLE_SALES)
                    ->where('distributor_id', $this->distributor()->id)
                    ->whereNull('deleted_at')
                    ->ignore($current?->getKey()),
            ],
        ], [
            'sales_name.required' => 'Nama sales wajib diisi.',
            'sales_phone.required' => 'No. HP wajib diisi.',
            'sales_phone.regex' => 'No. HP harus berupa angka 8-15 digit (boleh diawali +).',
            'sales_phone.unique' => 'No. HP ini sudah dipakai sales lain milik Anda.',
            'sales_email.email' => 'Format email tidak valid.',
            'sales_email.unique' => 'Email ini sudah terdaftar di Rasaconnect.',
        ]);

        return [
            'name' => $data['sales_name'],
            'phone' => $data['sales_phone'],
            'email' => filled($data['sales_email'] ?? null) ? strtolower($data['sales_email']) : User::internalSalesEmail(),
        ];
    }
}
