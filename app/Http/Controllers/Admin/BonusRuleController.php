<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BonusRule;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class BonusRuleController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            return DataTables::of(BonusRule::query()->withCount(['buyProducts', 'giftProducts']))
                ->addIndexColumn()
                ->addColumn('type_label', fn ($r) => e($r->typeLabel()))
                ->addColumn('rule_text', function ($r) {
                    $text = "Beli {$r->buy_qty} {$r->unitLabel()} → dapat {$r->gift_qty} {$r->unitLabel()}";
                    $text .= $r->is_multiple ? ' (kelipatan' . ($r->max_gift_qty ? ", maks {$r->max_gift_qty}" : '') . ')' : ' (sekali saja)';

                    return e($text);
                })
                ->addColumn('products_text', fn ($r) => "Syarat: {$r->buy_products_count} produk<br>Hadiah: {$r->gift_products_count} produk")
                ->addColumn('customers', fn ($r) => e(collect($r->customer_types)->map(fn ($t) => BonusRule::CUSTOMER_TYPES[$t] ?? $t)->implode(', ')))
                ->addColumn('period', fn ($r) => e($r->periodLabel()))
                ->addColumn('status_badge', fn ($r) => $r->is_active ? '<span class="label label-success">Aktif</span>' : '<span class="label label-danger">Tidak Aktif</span>')
                ->addColumn('action', function ($r) {
                    $html = '<a href="' . route('admin.bonus-rules.edit', $r) . '" class="btn btn-warning btn-xs"><i class="fa fa-edit"></i> Ubah</a> ';
                    $html .= '<form action="' . route('admin.bonus-rules.destroy', $r) . '" method="POST" style="display:inline-block" onsubmit="return confirm(\'Hapus aturan bonus ini?\')">'
                        . csrf_field() . method_field('DELETE')
                        . '<button type="submit" class="btn btn-danger btn-xs"><i class="fa fa-trash"></i> Hapus</button></form>';

                    return $html;
                })
                ->rawColumns(['products_text', 'status_badge', 'action'])
                ->make(true);
        }

        return view('admin.bonus_rules.index');
    }

    public function create()
    {
        return view('admin.bonus_rules.form', ['rule' => new BonusRule(['type' => 'fixed_gift', 'unit' => 'carton', 'is_multiple' => true, 'is_active' => true, 'customer_types' => []])]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        DB::transaction(function () use ($data) {
            $rule = BonusRule::create($data['rule']);
            $rule->buyProducts()->sync($data['buy']);
            $rule->giftProducts()->sync($data['gift']);
        });

        return redirect()->route('admin.bonus-rules.index')->with('success', 'Aturan bonus berhasil ditambahkan.');
    }

    public function edit(BonusRule $bonusRule)
    {
        $bonusRule->load(['buyProducts', 'giftProducts']);

        return view('admin.bonus_rules.form', ['rule' => $bonusRule]);
    }

    public function update(Request $request, BonusRule $bonusRule)
    {
        $data = $this->validated($request);
        DB::transaction(function () use ($data, $bonusRule) {
            $bonusRule->update($data['rule']);
            $bonusRule->buyProducts()->sync($data['buy']);
            $bonusRule->giftProducts()->sync($data['gift']);
        });

        return redirect()->route('admin.bonus-rules.index')->with('success', 'Aturan bonus berhasil diperbarui.');
    }

    public function destroy(BonusRule $bonusRule)
    {
        $bonusRule->delete();

        return redirect()->route('admin.bonus-rules.index')->with('success', 'Aturan bonus berhasil dihapus.');
    }

    public function searchProducts(Request $request)
    {
        $keyword = trim((string) $request->get('q', ''));
        $query = Product::query()->where('status', 'active')->orderByRaw('COALESCE(commercial_name, name) ASC');
        if ($keyword !== '') {
            $query->where(fn ($q) => $q->where('name', 'like', "%{$keyword}%")
                ->orWhere('code', 'like', "%{$keyword}%")
                ->orWhere('commercial_name', 'like', "%{$keyword}%"));
        }

        return response()->json(['results' => $query->limit(20)->get()->map(fn (Product $p) => ['id' => $p->id, 'text' => $p->full_name])->values()]);
    }

    /** @return array{rule: array<string, mixed>, buy: array<int, string>, gift: array<int, string>} */
    private function validated(Request $request): array
    {
        $v = $request->validate([
            'name' => 'required|string|max:150',
            'type' => ['required', Rule::in([BonusRule::TYPE_FIXED, BonusRule::TYPE_CHOICE])],
            'buy_qty' => 'required|integer|min:1',
            'gift_qty' => 'required|integer|min:1',
            'unit' => ['required', Rule::in(['carton', 'pcs'])],
            'max_gift_qty' => 'nullable|integer|min:1',
            'customer_types' => 'required|array|min:1',
            'customer_types.*' => Rule::in(array_keys(BonusRule::CUSTOMER_TYPES)),
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
            'notes' => 'nullable|string|max:1000',
            'buy_products' => 'required|array|min:1',
            'buy_products.*' => 'exists:products,id',
            'gift_products' => 'required|array|min:1',
            'gift_products.*' => 'exists:products,id',
        ], [
            'name.required' => 'Nama aturan wajib diisi.',
            'buy_qty.required' => 'Jumlah syarat beli wajib diisi.',
            'gift_qty.required' => 'Jumlah hadiah wajib diisi.',
            'customer_types.required' => 'Pilih minimal satu jenis customer.',
            'ends_at.after_or_equal' => 'Tanggal berakhir tidak boleh sebelum tanggal mulai.',
            'buy_products.required' => 'Pilih minimal satu produk syarat.',
            'gift_products.required' => 'Pilih minimal satu produk hadiah.',
        ]);

        if ($v['type'] === BonusRule::TYPE_FIXED && count($v['gift_products']) !== 1) {
            throw \Illuminate\Validation\ValidationException::withMessages(['gift_products' => 'Tipe "produk tertentu" harus tepat satu produk hadiah.']);
        }

        return [
            'rule' => [
                'name' => $v['name'], 'type' => $v['type'], 'buy_qty' => $v['buy_qty'], 'gift_qty' => $v['gift_qty'],
                'unit' => $v['unit'], 'is_multiple' => $request->boolean('is_multiple'),
                'max_gift_qty' => $request->boolean('is_multiple') ? ($v['max_gift_qty'] ?? null) : null,
                'customer_types' => array_values($v['customer_types']),
                'starts_at' => $v['starts_at'] ?? null, 'ends_at' => $v['ends_at'] ?? null,
                'is_active' => $request->boolean('is_active'), 'notes' => $v['notes'] ?? null,
            ],
            'buy' => array_values(array_unique($v['buy_products'])),
            'gift' => array_values(array_unique($v['gift_products'])),
        ];
    }
}
