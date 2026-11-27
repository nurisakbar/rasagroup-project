<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use App\Models\DistributorSalesperson;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * CRUD data sales milik distributor yang sedang login. Distributor hanya melihat dan mengubah sales miliknya.
 */
class DistributorSalesController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q'));

        $salespeople = $this->ownQuery()
            ->when($search !== '', fn ($q) => $q->where(fn ($w) => $w
                ->where('name', 'like', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%")))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('buyer.sales.index', ['salespeople' => $salespeople, 'search' => $search, 'distributor' => Auth::user()]);
    }

    public function create(): View
    {
        return view('buyer.sales.form', ['salesperson' => new DistributorSalesperson(), 'distributor' => Auth::user()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $salesperson = new DistributorSalesperson($this->validated($request));
        $salesperson->distributor_id = Auth::id();
        $salesperson->save();

        return redirect()->route('buyer.sales.index')->with('success', 'Sales berhasil ditambahkan.');
    }

    public function edit(string $sale): View
    {
        return view('buyer.sales.form', ['salesperson' => $this->findOwn($sale), 'distributor' => Auth::user()]);
    }

    public function update(Request $request, string $sale): RedirectResponse
    {
        $salesperson = $this->findOwn($sale);
        $salesperson->update($this->validated($request, $salesperson));

        return redirect()->route('buyer.sales.index')->with('success', 'Data sales berhasil diperbarui.');
    }

    public function destroy(string $sale): RedirectResponse
    {
        $salesperson = $this->findOwn($sale);
        $salesperson->delete();

        return redirect()->route('buyer.sales.index')->with('success', 'Sales ' . $salesperson->name . ' berhasil dihapus.');
    }

    private function ownQuery()
    {
        $user = Auth::user();
        abort_unless($user && $user->isDistributor(), 403);

        return DistributorSalesperson::where('distributor_id', $user->id);
    }

    private function findOwn(string $id): DistributorSalesperson
    {
        return $this->ownQuery()->whereKey($id)->firstOrFail();
    }

    /**
     * Field form memakai nama sales_name/sales_phone (bukan name/phone) agar browser tidak mengisi otomatis
     * dengan nama dan HP pemilik akun.
     */
    private function validated(Request $request, ?DistributorSalesperson $current = null): array
    {
        $request->merge(['sales_phone' => preg_replace('/[\s\-()]/', '', (string) $request->input('sales_phone'))]);

        $data = $request->validate([
            'sales_name' => ['required', 'string', 'max:150'],
            'sales_phone' => [
                'required', 'string', 'regex:/^\+?[0-9]{8,15}$/',
                Rule::unique('distributor_salespeople', 'phone')
                    ->where('distributor_id', Auth::id())
                    ->ignore($current?->getKey()),
            ],
        ], [
            'sales_name.required' => 'Nama sales wajib diisi.',
            'sales_phone.required' => 'No. HP wajib diisi.',
            'sales_phone.regex' => 'No. HP harus berupa angka 8-15 digit (boleh diawali +).',
            'sales_phone.unique' => 'No. HP ini sudah dipakai sales lain milik Anda.',
        ]);

        return ['name' => $data['sales_name'], 'phone' => $data['sales_phone']];
    }
}
