<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    public function searchSales(Request $request)
    {
        $search = $request->q;
        $query = User::where('role', 'sales')
            ->whereNotNull('sales_code')
            ->where('sales_code', '!=', '');
            
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('sales_code', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%");
            });
        }
        
        $sales = $query->limit(20)->get(['id', 'sales_code', 'name']);
        
        $results = [];
        foreach ($sales as $sale) {
            $results[] = [
                'id' => $sale->sales_code . ' - ' . $sale->name,
                'text' => $sale->sales_code . ' - ' . $sale->name
            ];
        }
        
        return response()->json(['results' => $results]);
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request, \App\Services\QadWhatsAppService $whatsappService): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'phone' => ['required', 'string', 'max:20', 'unique:'.User::class],
            'sales_code' => ['nullable', 'string', 'max:255'],
        ]);

        $waCode = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);

        $salesCode = $request->sales_code;
        if ($salesCode && strpos($salesCode, ' - ') !== false) {
            $parts = explode(' - ', $salesCode);
            $salesCode = trim($parts[0]);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'role' => !empty($salesCode) ? 'outlet' : 'buyer',
            'wa_verification_code' => $waCode,
            'is_potential_distributor' => $request->has('is_potential_distributor') ? 1 : 0,
            'sales_code' => $salesCode,
        ]);

        event(new Registered($user));

        Auth::login($user);

        if (env('BYPASS_WA_VERIFICATION', true)) {
            $user->wa_verified_at = now();
            $user->save();
            return redirect(route('dashboard', absolute: false));
        }

        // Dispatch WhatsApp Verification Job to queue
        \App\Jobs\SendWhatsAppVerificationJob::dispatch($user, $waCode);

        return redirect(route('wa.verify', absolute: false));
    }
}
