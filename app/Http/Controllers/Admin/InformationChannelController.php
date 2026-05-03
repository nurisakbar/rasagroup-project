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

            // Status filter
            if ($request->filled('status') && $request->status != '') {
                $query->where('is_active', $request->status == '1');
            }

            // Target audience filter
            if ($request->filled('target') && $request->target != '') {
                $query->where('target_audience', $request->target);
            }

            // Start date range filter
            if ($request->filled('start_from')) {
                $query->whereDate('start_date', '>=', $request->start_from);
            }
            if ($request->filled('start_until')) {
                $query->whereDate('start_date', '<=', $request->start_until);
            }

            // End date range filter
            if ($request->filled('end_from')) {
                $query->whereDate('end_date', '>=', $request->end_from);
            }
            if ($request->filled('end_until')) {
                $query->whereDate('end_date', '<=', $request->end_until);
            }

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('title_info', function ($channel) {
                    return '<strong>' . $channel->title . '</strong><br><small class="text-muted">' . $channel->slug . '</small>';
                })
                ->addColumn('audience', function ($channel) {
                    $audiences = [
                        'all' => '<span class="label label-primary">Semua</span>',
                        'distributor' => '<span class="label label-warning">Distributor</span>',
                        'customer' => '<span class="label label-info">Customer</span>',
                    ];
                    return $audiences[$channel->target_audience] ?? $channel->target_audience;
                })
                ->addColumn('date_start', function ($channel) {
                    return $channel->start_date ? $channel->start_date->format('d/m/Y') : '-';
                })
                ->addColumn('date_end', function ($channel) {
                    return $channel->end_date ? $channel->end_date->format('d/m/Y') : '-';
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
                ->rawColumns(['title_info', 'audience', 'status_badge', 'action'])
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
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'target_audience' => 'required|in:all,distributor,customer',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
        ]);

        $validated['is_active'] = $request->has('is_active');

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('information-channels', 'public');
        }

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
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'target_audience' => 'required|in:all,distributor,customer',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
        ]);

        $validated['is_active'] = $request->has('is_active');

        if ($request->hasFile('image')) {
            $informationChannel->deleteStoredImageFile();
            $validated['image'] = $request->file('image')->store('information-channels', 'public');
        }

        $informationChannel->update($validated);

        return back()->with('success', 'Saluran Informasi berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(InformationChannel $informationChannel)
    {
        $informationChannel->deleteStoredImageFile();
        $informationChannel->delete();

        return redirect()->route('admin.information-channels.index')
            ->with('success', 'Saluran Informasi berhasil dihapus.');
    }
}
