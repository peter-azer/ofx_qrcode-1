<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    // Redirect to Google for authentication
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->stateless()->redirect();
    }

    // Handle Google callback
    public function handleGoogleCallback()
    {
        try {
            // Retrieve user information from Google
            $googleUser = Socialite::driver('google')->stateless()->user();

            // Check if the user exists
            $user = User::where('email', $googleUser->getEmail())->first();

            if (!$user) {
                return response()->json([
                    'message' => 'User not found. Please sign up first.',
                ], 404);
            }

            // Log in the user
            Auth::login($user);

            return response()->json([
                'message' => 'User logged in successfully',
                'user' => $user,
                'token' => $user->createToken('GoogleLogin')->plainTextToken,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Authentication failed',
                'message' => $e->getMessage(),
            ], 500);
        }
    }



    public function handleGoogleSignUpOrLogin()
    {
        try {
            // Get Google user details
            $googleUser = Socialite::driver('google')->stateless()->user();

            // Check if the user exists in the database
            $user = User::where('email', $googleUser->getEmail())->first();

            if (!$user) {
                // Create a new user (Sign-Up)
                $user = User::create([
                    'name' => $googleUser->getName(),
                    'email' => $googleUser->getEmail(),
                    'google_id' => $googleUser->getId(),
                    'avatar' => $googleUser->getAvatar(),
                    'password' => bcrypt('random-password'), // You can use a random password or keep it null
                ]);
            }

            // Log in the user
            Auth::login($user);

            // Generate a token for API authentication
            $token = $user->createToken('GoogleAuth')->plainTextToken;

            return response()->json([
                'message' => 'User signed up or logged in successfully',
                'user' => $user,
                'token' => $token,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Authentication failed',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}

