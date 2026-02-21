<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Str;

class GoogleController extends Controller
{
    /**
     * Redirect the user to the Google authentication page.
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Obtain the user information from Google.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function handleGoogleCallback()
    {
        try {
            $user = Socialite::driver('google')->user();
            
            $findUser = User::where('google_id', $user->id)
                ->orWhere('email', $user->email)
                ->first();

            if ($findUser) {
                // Update google_id in case it was empty but email matched
                if (empty($findUser->google_id)) {
                    $findUser->update([
                        'google_id' => $user->id,
                        'google_token' => $user->token,
                        'google_refresh_token' => $user->refreshToken,
                    ]);
                } else {
                    $findUser->update([
                        'google_token' => $user->token,
                        'google_refresh_token' => $user->refreshToken,
                    ]);
                }

                Auth::login($findUser);
                return redirect()->intended('dashboard');
            } else {
                $newUser = User::create([
                    'name' => $user->name,
                    'email' => $user->email,
                    'google_id' => $user->id,
                    'google_token' => $user->token,
                    'google_refresh_token' => $user->refreshToken,
                    'password' => bcrypt(Str::random(16)),
                    'role' => 'buyer', // Default role
                ]);

                Auth::login($newUser);
                return redirect()->intended('dashboard');
            }
        } catch (Exception $e) {
            return redirect('login')->with('error', 'Something went wrong while logging in with Google: ' . $e->getMessage());
        }
    }
}
