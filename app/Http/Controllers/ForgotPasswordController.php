<?php

// app/Http/Controllers/ForgotPasswordController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use App\Mail\ResetPasswordEmail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
class ForgotPasswordController extends Controller
{
    // Step 1: Send Password Reset Link
    // public function sendResetLinkEmail(Request $request)
    // {
    //     $request->validate(['email' => 'required|email|exists:users,email']);
    
    //     // Get the user by email
    //     $user = User::where('email', $request->email)->first();
    
    //     $token = Password::createToken($user);
    
      
    //     $user->notify(new ResetPasswordNotification($token, $request->email));
      
    //     return response()->json(['message' => 'Password reset link sent.', 'token' =>$token], 200);
    // }


    public function sendResetLinkEmail(Request $request)
{
    $request->validate(['email' => 'required|email|exists:users,email']);
  
    // Get the user by email
    $user = User::where('email', $request->email)->first();

    // Create a password reset token
    $token = Password::createToken($user);

    // Create the reset URL
    $resetUrl = "https://ofx-qrcode.com/resetpassword?token={$token}&email={$request->email}";

    // Send the password reset email using the Mail facade and custom Blade view
    Mail::to($user->email)->send(new ResetPasswordEmail($resetUrl));

    return response()->json(['message' => 'Password reset link sent.'], 200);

}

    // Step 2: Reset the Password
    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'token' => 'required',
            'password' => 'required|min:8|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->save();
            }
        );

        return $status === Password::PASSWORD_RESET
            ? response()->json(['message' => 'Password reset successful.'], 200)
            : response()->json(['message' => 'Invalid token or email.'], 400);
    }






    
 

}
