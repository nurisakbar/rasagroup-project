<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DistributorApiClient;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DistributorApiClientController extends Controller
{
    public function store(Request $request, User $distributor): RedirectResponse
    {
        $this->authorizeAccess();
        abort_unless($distributor->isDistributor(), 404);

        $validated = $request->validate(['name' => ['required', 'string', 'max:100']]);
        $issued = DistributorApiClient::issueFor($distributor, $validated['name']);

        return $this->backToTab($distributor)
            ->with('success', 'API key berhasil dibuat.')
            ->with('new_api_key', $issued['plain_key']);
    }

    public function destroy(User $distributor, DistributorApiClient $client): RedirectResponse
    {
        $this->authorizeAccess();
        abort_unless($client->user_id === $distributor->id, 404);

        // Dinonaktifkan, bukan dihapus, agar penjualan tetap tertelusur ke key pengirimnya.
        $client->update(['is_active' => false]);

        return $this->backToTab($distributor)->with('success', 'API key dicabut.');
    }

    private function backToTab(User $distributor): RedirectResponse
    {
        return redirect()->route('admin.distributors.show', ['distributor' => $distributor->id, 'tab' => 'api']);
    }

    private function authorizeAccess(): void
    {
        abort_unless(auth('admin')->user()?->role === User::ROLE_SUPER_ADMIN, 403);
    }
}
