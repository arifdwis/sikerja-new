<?php

namespace App\Http\Controllers;

use App\Models\Faq;
use App\Models\Laman;
use App\Models\Slider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

use App\Models\Permohonan;
use App\Models\User;

class LandingController extends Controller
{
    public function home()
    {
        $sliders = Slider::where('is_active', true)->latest()->get();
        if ($sliders->isEmpty()) {
            $sliders = collect([]);
        }

        $faqs = Faq::take(5)->get();

        $stats = [
            'documents' => Permohonan::count(),
            'opds' => User::count(), // Simply counting users for now as proxy for partners
            'improvement' => 85,
            'uptime' => '24/7'
        ];

        return Inertia::render('Frontend/Landing/Home', [
            'canLogin' => Route::has('login'),
            'canRegister' => Route::has('register'),
            'sliders' => $sliders,
            'faqs' => $faqs,
            'stats' => $stats,
        ]);
    }

    public function about()
    {
        // Try to find a laman responsible for 'tentang' or 'about'
        // If not found, use the hardcoded view or partial content
        // For now, we will pass dynamic data to the existing view if available
        $page = Laman::where('slug', 'tentang-kami')->orWhere('slug', 'tentang-sikerja')->first();

        return Inertia::render('Frontend/Landing/About', [
            'page' => $page
        ]);
    }

    public function workflow()
    {
        // Static for now unless 'alur' laman exists
        return Inertia::render('Frontend/Landing/Workflow');
    }

    public function products()
    {
        // Static for now unless 'produk' laman exists
        return Inertia::render('Frontend/Landing/Products');
    }

    public function faq()
    {
        $faqs = Faq::all();
        return Inertia::render('Frontend/Landing/Faq', [
            'faqs' => $faqs
        ]);
    }

    public function page($slug)
    {
        $page = Laman::where('slug', $slug)->firstOrFail();

        return Inertia::render('Frontend/Landing/Page', [
            'page' => $page
        ]);
    }
}
