<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\RunMasterSyncJob;
use App\Support\MasterSyncProgress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MasterSyncController extends Controller
{
    private const SYNC_TYPES = [
        'qad_jubelio',
        'jubelio_hubs',
        'jubelio_stock',
        'jubelio_products',
    ];

    public function dispatch(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:' . implode(',', self::SYNC_TYPES),
        ]);

        $userId = Auth::guard('admin')->id() ?? Auth::id();
        $progress = MasterSyncProgress::create($validated['type'], $userId ? (string) $userId : null);

        RunMasterSyncJob::dispatch($progress->id(), $validated['type']);

        return response()->json([
            'sync_id' => $progress->id(),
        ]);
    }

    public function status(string $syncId)
    {
        $progress = MasterSyncProgress::find($syncId);

        if (! $progress) {
            return response()->json(['message' => 'Status sinkronisasi tidak ditemukan.'], 404);
        }

        return response()->json($progress->toArray());
    }
}
