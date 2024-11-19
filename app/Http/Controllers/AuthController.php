<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
// app/Http/Controllers/AuthController.php


use App\Mail\EmailVerificationCode;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Str;

class AuthController extends Controller
{




    public function verifyCode(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'verification_code' => 'required|string',
        ]);

        // Check if the verification code is valid and not expired
        $record = DB::table('email_verifications')
                    ->where('email', $request->email)
                    ->where('verification_code', $request->verification_code)
                    ->where('expires_at', '>', Carbon::now())
                    ->first();

        if (!$record) {
            return response()->json(['message' => 'Invalid or expired verification code'], 400);
        }

        // Verification successful, create the user with the stored data
        $user = User::create([
            'name'=> $record->name,
            'email' => $record->email,
            'phone' => $record->phone,
            'password' => $record->password,  // Already hashed
        ]);

        // Optionally delete the verification record after successful verification
        DB::table('email_verifications')->where('email', $request->email)->delete();

        return response()->json(['message' => 'User registered successfully', 'user' => $user], 201);
    }





    public function sendVerificationCode(Request $request)
    {
        $request->validate([
            'name'=> 'required|string',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string|unique:users',
            'password' => 'required|string|min:6',
        ]);

        $code = Str::random(6);

        // Store the code and temporary user data in the email_verifications table
        DB::table('email_verifications')->updateOrInsert(
            ['email' => $request->email],
            [

                'verification_code' => $code,
                'expires_at' => Carbon::now()->addMinutes(10),
                'name' => $request->name,
                'phone' => $request->phone,
                'password' => Hash::make($request->password),  // Store hashed password
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]
        );

        // Send the verification code via email
        Mail::to($request->email)->send(new EmailVerificationCode($code));

        return response()->json(['message' => 'Verification code sent to your email'], 200);
    }



public function signup(Request $request)
{
    $validator = Validator::make($request->all(), [
        'name' => 'required|string',
        'phone' => 'required|string|unique:users',
        'email' => 'required|string|email|unique:users',
        'password' => 'required|string|min:6',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'message' => 'Validation Error',
            'errors' => $validator->errors(),
        ], 400);
    }

    // Send verification code to the user's email
    $this->sendVerificationCode($request);

    return response()->json(['message' => 'Verification code sent to your email. Please verify to complete registration.'], 200);
}


    // Sign up method
    public function signsup(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string|unique:users',
            'email' => 'required|string|email|unique:users',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'errors' => $validator->errors(),
            ], 400);
        }
        $user = User::create([
            'phone' => $request->phone,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        return response()->json(['message' => 'User registered successfully', 'user' => $user], 201);
    }

    // Login method
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $token = $user->createToken('personalAccessToken')->plainTextToken;
       // Eager load the packages with pivot data
       $user->load('packages');



       return response()->json([
           'message' => 'Login successful',
           'token' => $token,
           'user' => $user,

       ], 200);
   }


    // Forget Password method
    public function forgetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $status = Password::sendResetLink($request->only('email'));

        return $status == Password::RESET_LINK_SENT
            ? response()->json(['message' => 'Password reset link sent'], 200)
            : response()->json(['message' => 'Error sending password reset link'], 500);
    }


    public function logout(Request $request)
    {
        // Revoke the current user's token
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully'], 200);
    }




    public function getUserData(Request $request)
    {
        // Assuming the authenticated user
        $user = $request->user();

        // Retrieve user's packages with pivot data
        $userPackages = $user->packages->map(function ($package) {
            return [

                'name' => $package->name,
                'qrcode_limit' => $package->pivot->qrcode_limit,
                'start_date' => $package->pivot->start_date,
                'end_date' => $package->pivot->end_date,
                'is_enable' =>$package->pivot->is_enable,
            ];
        });

        // Combine user data and packages
        $userData = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'user_packages' => $userPackages,
        ];

        return response()->json([
            'status' => 'success',
            'user_data' => $userData,
        ], 200);
    }
}

