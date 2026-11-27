<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use App\Models\DistributorSalesperson;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * CRUD data sales milik distributor yang sedang login. Distributor hanya melihat dan mengubah sales miliknya.
 */
class DistributorSalesController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q'));
        $status = $request->query('status');

        $salespeople = $this->ownQuery()
            ->when($search !== '', fn ($q) => $q->where(fn ($w) => $w
                ->where('name', 'like', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('area', 'like', "%{$search}%")))
            ->when(in_array($status, ['active', 'inactive'], true), fn ($q) => $q->where('is_active', $status === 'active'))
            ->orderByDesc('is_active')
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('buyer.sales.index', compact('salespeople', 'search', 'status'));
    }

    public function create(): View
    {
        return view('buyer.sales.form', ['salesperson' => new DistributorSalesperson(['is_active' => true])]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->ownQuery()->create($this->validated($request) + ['distributor_id' => Auth::id()]);

        return redirect()->route('buyer.sales.index')->with('success', 'Sales berhasil ditambahkan.');
    }

    public function edit(string $sale): View
    {
        return view('buyer.sales.form', ['salesperson' => $this->findOwn($sale)]);
    }

    public function update(Request $request, string $sale): RedirectResponse
    {
        $this->findOwn($sale)->update($this->validated($request));

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

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'phone' => ['required', 'string', 'max:30', 'regex:/^[0-9+\-\s()]{6,30}$/'],
            'email' => ['nullable', 'email', 'max:150'],
            'area' => ['nullable', 'string', 'max:150'],
            'is_active' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ], [
            'name.required' => 'Nama sales wajib diisi.',
            'phone.required' => 'No. HP wajib diisi.',
            'phone.regex' => 'No. HP hanya boleh berisi angka, spasi, +, -, atau tanda kurung.',
            'email.email' => 'Format email tidak valid.',
        ]);
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
