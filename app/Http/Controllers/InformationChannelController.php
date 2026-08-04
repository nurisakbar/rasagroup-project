<?php

namespace App\Http\Controllers;

use App\Models\InformationChannel;
use Illuminate\Http\Request;

class InformationChannelController extends Controller
{
    /**
     * Display a listing of information channels.
     */
    public function index()
    {
        $query = InformationChannel::where('is_active', true);

        if (!auth()->check()) {
            $query->whereIn('target_audience', ['all', 'customer']);
        } else {
            // Jika sudah login, cek role distributor
            $user = auth()->user();
            if ($user->role === 'distributor') {
                $query->whereIn('target_audience', ['all', 'distributor']);
            } else {
                $query->whereIn('target_audience', ['all', 'customer']);
            }
        }

        $channels = $query->orderBy('created_at', 'desc')->paginate(10);

        return view('themes.nest.information-channels.index', compact('channels'));
    }

    /**
     * Display the specified information channel.
     */
    public function show($slug)
    {
        $query = InformationChannel::where('slug', $slug)->where('is_active', true);

        if (!auth()->check()) {
            $query->whereIn('target_audience', ['all', 'customer']);
        } else {
            $user = auth()->user();
            if ($user->role === 'distributor') {
                $query->whereIn('target_audience', ['all', 'distributor']);
            } else {
                $query->whereIn('target_audience', ['all', 'customer']);
            }
        }

        $channel = $query->firstOrFail();

        return view('themes.nest.information-channels.show', compact('channel'));
    }

    /**
     * Store a comment for the information channel.
     */
    public function storeComment(Request $request, $slug)
    {
        $request->validate([
            'content' => 'required|string|max:1000',
        ]);

        $channel = InformationChannel::where('slug', $slug)->firstOrFail();

        $channel->comments()->create([
            'user_id' => auth()->id(),
            'content' => $request->content,
        ]);

        return back()->with('success', 'Komentar berhasil ditambahkan.');
    }
}
