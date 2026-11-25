<?php

namespace App\Http\Controllers\Distributor;

use App\Http\Controllers\Controller;
use App\Models\Warehouse;
use App\Services\EkspedisiKuService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Lokasi gudang distributor: sumber pengiriman dan titik ambil subdist.
 * Satu distributor boleh punya beberapa gudang. Dikelola distributor sendiri dan langsung berlaku
 * (tanpa persetujuan admin). Terpisah dari alamat tujuan kirim distributor di tabel addresses.
 * Pesanan subdist tidak masuk QAD/WMS, jadi gudang tambahan tidak memerlukan kode lokasi QAD.
 */
class WarehouseLocationController extends Controller
{
    /** Peran khusus agar gudang buatan distributor tidak terpilih sebagai hub terdekat pembeli biasa. */
    public const TARGET_ROLE = 'subdist';

    public function __construct(protected EkspedisiKuService $ekspedisiku)
    {
    }

    public function index()
    {
        $warehouses = Auth::user()->distributorWarehouses();
        $primaryId = Auth::user()->warehouse_id;

        return view('distributor.warehouse-location.index', compact('warehouses', 'primaryId'));
    }

    public function create()
    {
        return view('distributor.warehouse-location.edit', [
            'warehouse' => new Warehouse(['is_active' => true]),
            'provinces' => $this->provinces(),
            'isPrimary' => false,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = Auth::user();
        $validated = $this->validated($request, true);

        $warehouse = Warehouse::create($validated + [
            'slug' => $this->uniqueSlug($validated['name']),
            'is_active' => true,
            'owner_distributor_id' => $user->id,
            'target_role' => [self::TARGET_ROLE],
            'send_to_jubelio' => false,
        ]);

        Log::info('Distributor menambah gudang', ['user_id' => $user->id, 'warehouse_id' => $warehouse->id, 'data' => $validated]);

        return redirect()->route('distributor.warehouse-location.index')
            ->with('success', "Gudang \"{$warehouse->name}\" ditambahkan. Atur stoknya di menu Stok agar subdist bisa memesan.");
    }

    public function edit(string $id)
    {
        $warehouse = $this->ownWarehouse($id);

        return view('distributor.warehouse-location.edit', [
            'warehouse' => $warehouse,
            'provinces' => $this->provinces(),
            'isPrimary' => $this->isPrimary($warehouse),
        ]);
    }

    public function update(Request $request, string $id): RedirectResponse
    {
        $warehouse = $this->ownWarehouse($id);
        // Nama gudang utama diatur admin; gudang tambahan boleh diganti distributor.
        $validated = $this->validated($request, ! $this->isPrimary($warehouse));

        $before = $warehouse->only(array_keys($validated));
        $warehouse->update($validated);

        Log::info('Distributor mengubah lokasi gudang', [
            'user_id' => Auth::id(),
            'warehouse_id' => $warehouse->id,
            'before' => $before,
            'after' => $warehouse->only(array_keys($validated)),
        ]);

        return redirect()->route('distributor.warehouse-location.index')
            ->with('success', 'Lokasi gudang berhasil disimpan dan langsung berlaku untuk pesanan subdist.');
    }

    public function toggle(string $id): RedirectResponse
    {
        $warehouse = $this->ownWarehouse($id);
        $warehouse->update(['is_active' => ! $warehouse->is_active]);

        Log::info('Distributor mengubah status gudang', [
            'user_id' => Auth::id(), 'warehouse_id' => $warehouse->id, 'is_active' => $warehouse->is_active,
        ]);

        return redirect()->route('distributor.warehouse-location.index')
            ->with('success', $warehouse->is_active ? 'Gudang diaktifkan.' : 'Gudang dinonaktifkan. Subdist tidak lagi bisa memilihnya.');
    }

    private function ownWarehouse(string $id): Warehouse
    {
        $warehouse = Auth::user()->distributorWarehouses()->firstWhere('id', $id);
        abort_unless($warehouse, 404);

        return $warehouse;
    }

    private function isPrimary(Warehouse $warehouse): bool
    {
        return (string) $warehouse->id === (string) Auth::user()->warehouse_id;
    }

    private function provinces(): array
    {
        return $this->ekspedisiku->getProvinces()['data'] ?? [];
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'gudang';
        $slug = $base;
        $i = 2;
        while (Warehouse::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $i++;
        }

        return $slug;
    }

    private function validated(Request $request, bool $withName): array
    {
        $rules = [
            'address' => ['required', 'string', 'min:10', 'max:500'],
            'province_id' => ['required', 'string'],
            'regency_id' => ['required', 'string'],
            'district_id' => ['required', 'string'],
            'village_id' => ['nullable', 'string'],
            'postal_code' => ['nullable', 'string', 'max:10'],
            'phone' => ['required', 'string', 'max:20'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
        ];
        if ($withName) {
            $rules = ['name' => ['required', 'string', 'min:3', 'max:100']] + $rules;
        }

        return $request->validate($rules, [
            'name.required' => 'Nama gudang wajib diisi.',
            'name.min' => 'Nama gudang minimal 3 karakter.',
            'address.required' => 'Alamat lengkap gudang wajib diisi.',
            'address.min' => 'Alamat gudang minimal 10 karakter.',
            'province_id.required' => 'Provinsi wajib dipilih.',
            'regency_id.required' => 'Kabupaten/Kota wajib dipilih.',
            'district_id.required' => 'Kecamatan wajib dipilih.',
            'phone.required' => 'Nomor telepon gudang wajib diisi.',
            'latitude.between' => 'Latitude harus di antara -90 dan 90.',
            'longitude.between' => 'Longitude harus di antara -180 dan 180.',
        ]);
    }
}
