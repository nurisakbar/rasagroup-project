<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PageController extends Controller
{
    /**
     * Show the about page.
     */
    public function about()
    {
        return view('pages.about');
    }

    /**
     * Show the contact page.
     */
    public function contact()
    {
        return view('pages.contact');
    }

    /**
     * Handle contact form submission.
     */
    public function sendContact(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:2000',
        ]);

        // Here you can add logic to:
        // - Send email notification
        // - Store in database
        // - Send to external service
        
        // For now, we'll just return with a success message
        return back()->with('success', 'Terima kasih! Pesan Anda telah berhasil dikirim. Tim kami akan menghubungi Anda segera.');
    }
}

