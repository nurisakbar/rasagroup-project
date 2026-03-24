<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InformationChannel;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class InformationChannelController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = InformationChannel::query();

            if ($request->filled('status') && $request->status != '') {
                $query->where('is_active', $request->status == '1');
            }

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('title_info', function ($channel) {
                    return '<strong>' . $channel->title . '</strong><br><small class="text-muted">' . $channel->slug . '</small>';
                })
                ->addColumn('description_text', function ($channel) {
                    return Str::limit(strip_tags($channel->description), 100);
                })
                ->addColumn('status_badge', function ($channel) {
                    if ($channel->is_active) {
                        return '<span class="label label-success">Aktif</span>';
                    }
                    return '<span class="label label-danger">Nonaktif</span>';
                })
                ->addColumn('action', function ($channel) {
                    $editUrl = route('admin.information-channels.edit', $channel);
                    $deleteUrl = route('admin.information-channels.destroy', $channel);
                    
                    return '
                        <a href="' . $editUrl . '" class="btn btn-warning btn-xs" title="Edit">
                            <i class="fa fa-edit"></i>
                        </a>
                        <form action="' . $deleteUrl . '" method="POST" style="display: inline-block;" class="delete-form">
                            ' . csrf_field() . method_field('DELETE') . '
                            <button type="submit" class="btn btn-danger btn-xs" title="Hapus" onclick="return confirm(\'Anda yakin ingin menghapus data ini?\')">
                                <i class="fa fa-trash"></i>
                            </button>
                        </form>
                    ';
                })
                ->rawColumns(['title_info', 'status_badge', 'action'])
                ->make(true);
        }

        return view('admin.information-channels.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.information-channels.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:information_channels,slug',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['title']);
        }

        $validated['is_active'] = $request->has('is_active');

        InformationChannel::create($validated);

        return redirect()->route('admin.information-channels.index')
            ->with('success', 'Saluran Informasi berhasil dibuat.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(InformationChannel $informationChannel)
    {
        return view('admin.information-channels.edit', ['channel' => $informationChannel]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, InformationChannel $informationChannel)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:information_channels,slug,' . $informationChannel->id,
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['title']);
        }

        $validated['is_active'] = $request->has('is_active');

        $informationChannel->update($validated);

        return back()->with('success', 'Saluran Informasi berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(InformationChannel $informationChannel)
    {
        $informationChannel->delete();

        return redirect()->route('admin.information-channels.index')
            ->with('success', 'Saluran Informasi berhasil dihapus.');
    }
}
