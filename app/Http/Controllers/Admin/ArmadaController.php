<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Armada;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class ArmadaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = Armada::with('hub')->latest();
            
            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('hub_name', function ($armada) {
                    return $armada->hub ? $armada->hub->name : '-';
                })
                ->addColumn('action', function ($armada) {
                    $editUrl = route('admin.armadas.edit', $armada);
                    $deleteUrl = route('admin.armadas.destroy', $armada);
                    
                    return '
                        <a href="' . $editUrl . '" class="btn btn-warning btn-xs" title="Edit">
                            <i class="fa fa-edit"></i> Edit
                        </a>
                        <form action="' . $deleteUrl . '" method="POST" style="display:inline;" onsubmit="return confirm(\'Yakin ingin menghapus data ini?\');">
                            ' . csrf_field() . '
                            ' . method_field('DELETE') . '
                            <button type="submit" class="btn btn-danger btn-xs" title="Hapus">
                                <i class="fa fa-trash"></i> Hapus
                            </button>
                        </form>
                    ';
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('admin.armadas.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $hubs = Warehouse::orderBy('name')->get();
        return view('admin.armadas.create', compact('hubs'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'hub_id' => 'required|exists:warehouses,id',
            'description' => 'nullable|string',
        ]);

        Armada::create($validated);

        return redirect()->route('admin.armadas.index')->with('success', 'Armada pengiriman berhasil ditambahkan.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Armada $armada)
    {
        $hubs = Warehouse::orderBy('name')->get();
        return view('admin.armadas.edit', compact('armada', 'hubs'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Armada $armada)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'hub_id' => 'required|exists:warehouses,id',
            'description' => 'nullable|string',
        ]);

        $armada->update($validated);

        return redirect()->route('admin.armadas.index')->with('success', 'Armada pengiriman berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Armada $armada)
    {
        $armada->delete();

        return redirect()->route('admin.armadas.index')->with('success', 'Armada pengiriman berhasil dihapus.');
    }
}
