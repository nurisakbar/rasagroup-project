<?php

namespace App\Http\Controllers;

use App\Models\Page;

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
     * Show a dynamic page from database.
     */
    public function show($slug)
    {
        $page = Page::where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        return view('pages.show', compact('page'));
    }
}

