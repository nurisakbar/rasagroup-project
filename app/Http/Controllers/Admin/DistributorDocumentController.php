<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DistributorDocument;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DistributorDocumentController extends Controller
{
    public function store(Request $request, User $distributor): RedirectResponse
    {
        if (! $distributor->isDistributor()) {
            abort(404);
        }

        $validated = $request->validate([
            'nama_dokumen' => ['required', 'string', 'max:255'],
            'keterangan' => ['nullable', 'string', 'max:5000'],
            'file' => ['required', 'file', 'max:10240', 'mimes:pdf,doc,docx,xls,xlsx,csv,txt,jpg,jpeg,png,gif,webp,zip,rar'],
        ], [
            'file.required' => 'Berkas dokumen wajib diunggah.',
            'file.max' => 'Ukuran berkas maksimal 10 MB.',
        ]);

        $path = $request->file('file')->store('distributor-documents/'.$distributor->id, 'public');

        DistributorDocument::query()->create([
            'user_id' => $distributor->id,
            'nama_dokumen' => $validated['nama_dokumen'],
            'keterangan' => $validated['keterangan'] ?? null,
            'file_path' => $path,
        ]);

        return back()->with('success', 'Dokumen berhasil ditambahkan.');
    }

    public function update(Request $request, User $distributor, DistributorDocument $document): RedirectResponse
    {
        if (! $distributor->isDistributor() || $document->user_id !== $distributor->id) {
            abort(404);
        }

        $validated = $request->validate([
            'nama_dokumen' => ['required', 'string', 'max:255'],
            'keterangan' => ['nullable', 'string', 'max:5000'],
            'file' => ['nullable', 'file', 'max:10240', 'mimes:pdf,doc,docx,xls,xlsx,csv,txt,jpg,jpeg,png,gif,webp,zip,rar'],
        ], [
            'file.max' => 'Ukuran berkas maksimal 10 MB.',
        ]);

        $data = [
            'nama_dokumen' => $validated['nama_dokumen'],
            'keterangan' => $validated['keterangan'] ?? null,
        ];

        if ($request->hasFile('file')) {
            $document->deleteStoredFile();
            $data['file_path'] = $request->file('file')->store('distributor-documents/'.$distributor->id, 'public');
        }

        $document->update($data);

        return back()->with('success', 'Dokumen berhasil diperbarui.');
    }

    public function destroy(User $distributor, DistributorDocument $document): RedirectResponse
    {
        if (! $distributor->isDistributor() || $document->user_id !== $distributor->id) {
            abort(404);
        }

        $document->delete();

        return back()->with('success', 'Dokumen berhasil dihapus (soft delete).');
    }
}
