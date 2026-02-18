<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WebsitePopup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;

class WebsitePopupController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = WebsitePopup::query();

            if ($request->filled('status') && $request->status != '') {
                $query->where('is_active', $request->status == '1');
            }

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('image_display', function ($popup) {
                    if ($popup->image) {
                        return '<img src="' . asset('storage/' . $popup->image) . '" style="height: 50px; width: auto; max-width: 100px; object-fit: cover; border-radius: 5px;" alt="' . $popup->name . '">';
                    }
                    return '<i class="fa fa-image fa-2x text-muted"></i>';
                })
                ->addColumn('status_badge', function ($popup) {
                    if ($popup->is_active) {
                        return '<span class="label label-success">Aktif</span>';
                    }
                    return '<span class="label label-danger">Nonaktif</span>';
                })
                ->addColumn('action', function ($popup) {
                    $editUrl = route('admin.website-popups.edit', $popup);
                    $deleteUrl = route('admin.website-popups.destroy', $popup);
                    
                    return '
                        <a href="' . $editUrl . '" class="btn btn-warning btn-xs" title="Edit">
                            <i class="fa fa-edit"></i>
                        </a>
                        <form action="' . $deleteUrl . '" method="POST" style="display: inline-block;" class="delete-form">
                            ' . csrf_field() . method_field('DELETE') . '
                            <button type="submit" class="btn btn-danger btn-xs" title="Hapus">
                                <i class="fa fa-trash"></i>
                            </button>
                        </form>
                    ';
                })
                ->rawColumns(['image_display', 'status_badge', 'action'])
                ->make(true);
        }

        return view('admin.website_popups.index');
    }

    public function create()
    {
        return view('admin.website_popups.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'url' => 'nullable|url|max:255',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_active' => 'boolean',
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('popups', 'public');
        }

        $validated['is_active'] = $request->has('is_active');

        WebsitePopup::create($validated);

        return redirect()->route('admin.website-popups.index')
            ->with('success', 'Pop up berhasil ditambahkan.');
    }

    public function edit(WebsitePopup $websitePopup)
    {
        return view('admin.website_popups.edit', ['popup' => $websitePopup]);
    }

    public function update(Request $request, WebsitePopup $websitePopup)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'url' => 'nullable|url|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_active' => 'boolean',
        ]);

        if ($request->hasFile('image')) {
            if ($websitePopup->image) {
                Storage::disk('public')->delete($websitePopup->image);
            }
            $validated['image'] = $request->file('image')->store('popups', 'public');
        }

        $validated['is_active'] = $request->has('is_active');

        $websitePopup->update($validated);

        return back()->with('success', 'Pop up berhasil diperbarui.');
    }

    public function destroy(WebsitePopup $websitePopup)
    {
        if ($websitePopup->image) {
            Storage::disk('public')->delete($websitePopup->image);
        }

        $websitePopup->delete();

        return redirect()->route('admin.website-popups.index')
            ->with('success', 'Pop up berhasil dihapus.');
    }
}
