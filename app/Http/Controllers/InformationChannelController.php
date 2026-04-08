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
        $channels = InformationChannel::where('is_active', true)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('themes.nest.information-channels.index', compact('channels'));
    }

    /**
     * Display the specified information channel.
     */
    public function show($slug)
    {
        $channel = InformationChannel::where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

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
