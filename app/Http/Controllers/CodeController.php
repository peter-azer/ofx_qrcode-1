<?php

namespace App\Http\Controllers;


use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\code;

class CodeController extends Controller
{

    public function store(Request $request,$packege )
    {
        // Custom validation messages
        $messages = [
            'expires_at.required' => 'The expiration date field is required.',
            'expires_at.date' => 'The expiration date must be a valid date format. Example format: YYYY-MM-DD.',
        ];

        // Validate the request
        $validator = Validator::make($request->all(), [
            'expires_at' => 'required|date',
        ], $messages);

        // Check if validation fails
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Create the new code
        $code = code::create([
           // Initialize with an empty string
            'expires_at' => $request->expires_at,
            'user_id' => '0',
            'package_id' => $packege,

        ]);


        return response()->json(['message' => 'Code created successfully', 'code' => $code], 201);
    }



    public function validateCode(Request $request)
    {
        $user = $request->user();
        $validator = Validator::make($request->all(), [
            'code' => 'required|string',
            'package_id' => 'required|exists:packages,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $code = Code::where('code', $request->code)->first();

        if (!$code) {
            return response()->json(['message' => 'Invalid code.'], 404);
        }

        if (Carbon::now()->greaterThan($code->expires_at)) {
            return response()->json(['message' => 'Code has expired.'], 410);
        }

        if ($code->type === 'used') {
            return response()->json(['message' => 'Code is already used.'], 400);
        }
         log($code->package_id );
         log($request->package_id );
        if ($code->package_id !== $request->package_id) {
            return response()->json(['message' => 'Package mismatch.'], 400);
        }

        // Check if the user is already subscribed to the package
        $existingSubscription = Code::where('user_id', $user->id)
            ->where('package_id', $request->package_id)
            ->where('type', 'used')
            ->first();

        if ($existingSubscription) {
            return response()->json(['message' => 'You already have a code for this package.'], 400);
        }

        // If the code type is 'notused', update fields and set type to 'used'
        if ($code->type === 'notused') {
            $code->user_id = $user->id;
            // $code->package_id = $request->package_id;
            $code->type = 'used';
            $code->save();

            return response()->json(['message' => 'Code validated and updated successfully.'], 200);
        }

        return response()->json(['message' => 'Unknown error occurred.'], 500);
    }







    public function checkUserCodeStatus( request $request , $package_id)
    {
        $user = $request->user();
        // Retrieve the code associated with the user and lesson
        $code = Code::where('user_id', $user->id)
            ->where('package_id', $package_id)
            ->first();

        if (!$code) {
            return response()->json(['message' => 'No code found for this user and package.'], 404);
        }

        // Check if the code has expired
        if (Carbon::now()->greaterThan($code->expires_at)) {
            return response()->json(['message' => 'Code has expired.'], 410);
        }


        return response()->json(['message' => 'User has a valid code.', 'code' => $code], 200);
    }
}
