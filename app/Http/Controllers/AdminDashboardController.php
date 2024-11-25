<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
    public function getAllUsersWithPackages()
    {
        $users = User::with(['packages', 'qrcode'])->get();

        $data = $users->map(function ($user) {
            if ($user->packages->isNotEmpty()) {
                return [
                    'user' => $user->only(['id', 'name', 'email']),
                    'packages' => $user->packages->map(function ($package) {
                        return [
                            'name' => $package->name,
                            'qrcode_limit' => $package->pivot->qrcode_limit,
                            'start_date' => $package->pivot->start_date,
                            'end_date' => $package->pivot->end_date,
                            'is_enable' => $package->pivot->is_enable,
                        ];
                    }),
                    'qrcode_count' => $user->qrcode->count(),
                ];
            }

            return [
                'user' => $user->only(['id', 'name', 'email']),
                'message' => 'This user is not subscribed yet.',
            ];
        });

        return response()->json(['data' => $data], 200);
    }



    public function getEachUserWithQrCodes()
    {
        $users = User::with('qrcode')->get();

        $data = $users->map(function ($user) {
            return [
                'user' => $user->only(['id', 'name', 'email']),
                'qrcodes' => $user->qrcode->map(function ($qrcode) {
                    return [
                        'id' => $qrcode->id,
                        'qrcode' => $qrcode->qrcode, 
                        'link' => $qrcode->link, 
                        'scan_count' => $qrcode->scan_count,
                    ];
                }),
            ];
        });

        return response()->json(['data' => $data], 200);
    }
}
