<?php

namespace App\Http\Controllers;

use App\Models\contact_us;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ContactUsController extends Controller
{
    public function store(Request $request)
    {
        // Validate the request data
        $validatedData = $request->validate([
            'email' => 'required|email',
            'message' => 'required|string',
        ]);

        Mail::raw($validatedData['message'], function ($message) use ($validatedData) {
            $message->to('ofxqrcod@ofx-qrcode.com')
                ->from($validatedData['email']) // Use the 'from' address from .env
                ->replyTo($validatedData['email']) // Set reply-to as the user's email
                ->subject('OFX_QrCode Contact Us ');
        });

        // Save the message to the ContactUs model
        $contact = new contact_us();
        $contact->email = $validatedData['email'];
        $contact->message = $validatedData['message'];
        $contact->save();

        return response()->json([
            'message' => 'Your message has been sent and saved successfully.',
            'data' => [
                'email' => $validatedData['email'],
                'message' => $validatedData['message'],
            ],
        ], 201);
    }
}
