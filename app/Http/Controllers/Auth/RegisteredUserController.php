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
    /** Jenis pendaftaran lewat link distributor. */
    private const REGISTER_TYPES = [User::ROLE_OUTLET, User::ROLE_SUBDIST];

    /**
     * Link pendaftaran distributor:
     * - /register?kode=DST00003&jenis=outlet&sales=MCR090   (outlet, atas nama sales milik distributor)
     * - /register?kode=DST00003&jenis=subdist&sales=MCR090  (subdist, atas nama sales milik distributor)
     * - /register?kode=DST00003&jenis=subdist  atau link lama /register?distributor=DST00003  (subdist tanpa sales)
     */
    public function create(Request $request): View
    {
        $distributorCode = $request->query('kode', $request->query('distributor'));
        $referral = $this->resolveReferral($distributorCode, $request->query('jenis'), $request->query('sales'));

        return view('auth.register', [
            'referringDistributor' => $referral['distributor'] ?? null,
            'registerType' => $referral['type'] ?? null,
            'referralSales' => $referral['sales'] ?? null,
            'invalidDistributorCode' => filled($distributorCode) && $referral === null,
        ]);
    }

    private function findReferringDistributor(?string $code): ?User
    {
        $distributor = User::findDistributorBySalesCode($code);

        return $distributor?->canReferOutlets() ? $distributor : null;
    }

    /**
     * Distributor, jenis, dan sales dari link pendaftaran; null bila link tidak valid.
     * Sales harus milik distributor tersebut dan aktif. Jenis outlet wajib ada sales (outlet tercatat ke kode sales).
     *
     * @return array{distributor: User, type: string, sales: ?User}|null
     */
    private function resolveReferral(?string $distributorCode, ?string $type, ?string $salesCode): ?array
    {
        $distributor = $this->findReferringDistributor($distributorCode);
        if (! $distributor) {
            return null;
        }

        $type = strtolower(trim((string) $type)) ?: User::ROLE_SUBDIST;
        if (! in_array($type, self::REGISTER_TYPES, true)) {
            return null;
        }

        $sales = null;
        if (filled($salesCode)) {
            $sales = User::where('role', User::ROLE_SALES)
                ->where('distributor_id', $distributor->id)
                ->where('sales_code', strtoupper(trim($salesCode)))
                ->where('is_active', true)
                ->first();
            if (! $sales) {
                return null;
            }
        }

        if ($type === User::ROLE_OUTLET && ! $sales) {
            return null;
        }

        return ['distributor' => $distributor, 'type' => $type, 'sales' => $sales];
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
            'terms' => ['accepted'],
            'sales_code' => ['nullable', 'string', 'max:255'],
            'distributor_code' => ['nullable', 'string', 'max:20', function (string $attribute, mixed $value, \Closure $fail) use ($request) {
                if (! $this->resolveReferral($value, $request->input('register_type'), $request->input('referral_sales_code'))) {
                    $fail('Link pendaftaran distributor tidak valid.');
                }
            }],
            'register_type' => ['nullable', 'string', 'in:' . implode(',', self::REGISTER_TYPES)],
            'referral_sales_code' => ['nullable', 'string', 'max:50'],
        ], [
            'terms.accepted' => 'Anda harus menyetujui Syarat & Ketentuan sebelum mendaftar.',
        ]);

        $waCode = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);

        $salesCode = $request->sales_code;
        if ($salesCode && strpos($salesCode, ' - ') !== false) {
            $parts = explode(' - ', $salesCode);
            $salesCode = trim($parts[0]);
        }

        // Link distributor: jenis subdist => tercatat ke kode distributor; jenis outlet => tercatat ke kode sales
        // milik distributor. Tanpa link: kode sales => outlet; tanpa kode => ecom.
        $referral = $request->filled('distributor_code')
            ? $this->resolveReferral($request->distributor_code, $request->input('register_type'), $request->input('referral_sales_code'))
            : null;
        $recruitedBySalesId = $referral['sales']?->id ?? null;

        if ($referral) {
            $salesCode = $referral['type'] === User::ROLE_SUBDIST
                ? $referral['distributor']->distributor_sales_code
                : $referral['sales']->sales_code;
        }

        $role = match (true) {
            $referral !== null => $referral['type'],
            ! empty($salesCode) => User::ROLE_OUTLET,
            default => User::ROLE_ECOM,
        };

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'role' => $role,
            'wa_verification_code' => $waCode,
            'is_potential_distributor' => $request->has('is_potential_distributor') ? 1 : 0,
            'sales_code' => $salesCode,
            // Outlet tercatat di hub milik sales yang kodenya dipakai saat daftar.
            'hub_id' => User::hubIdForSalesCode($salesCode),
        ]);

        if ($recruitedBySalesId) {
            $user->forceFill(['recruited_by_sales_id' => $recruitedBySalesId])->save();
        }

        event(new Registered($user));

        if (env('BYPASS_WA_VERIFICATION', true)) {
            $user->wa_verified_at = now();
            $user->save();
        }
        // OTP WhatsApp tidak dikirim di sini: dikirim saat pengguna membuka halaman verifikasi WhatsApp.

        // Halaman khusus berisi instruksi aktivasi lewat email (email disimpan di session, bukan di URL).
        $request->session()->put('registered_email', $user->email);

        return redirect()->route('register.success');
    }

    /**
     * Halaman "Pendaftaran berhasil": instruksi cek email untuk aktivasi akun.
     */
    public function success(Request $request): View|RedirectResponse
    {
        $email = $request->session()->get('registered_email');
        if (! $email) {
            return redirect()->route('login');
        }

        return view('auth.register-success', ['email' => $email]);
    }
}
