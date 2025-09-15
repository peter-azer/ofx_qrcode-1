<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use Illuminate\Support\Facades\DB;
use App\Models\QrCodeModel;
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
                    'user' => $user->only(['id', 'name', 'email', 'created_at']),
                    'packages' => $user->packages->map(function ($package) {
                        return [
                            'name' => $package->name,
                            'qrcode_limit' => $package->pivot->qrcode_limit,
                            'start_date' => $package->pivot->start_date,
                            'end_date' => $package->pivot->end_date,
                            'is_enable' => $package->pivot->is_enable,
                            'created_at' => $package->pivot->created_at,
                        ];
                    }),
                    'qrcode_count' => $user->qrcode->count(),
                ];
            }

            return [
                'user' => $user->only(['id', 'name', 'email', 'created_at']),
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






    public function getQrCodeStats()
    {
      
        $totalUsers = User::count();


        $totalQRCodes = QrCodeModel::count();


        $activeQRCodes = QrCodeModel::where('is_active', 1)->count();

        $inactiveQRCodes = QrCodeModel::where('is_active', 0)->count();

        $enabledPackages = DB::table('user_packages')->where('is_enable', 1)->count();

        $disabledPackages = DB::table('user_packages')->where('is_enable', 0)->count();
    
 
        return response()->json([
            'enabled_packages' => $enabledPackages,
            'disabled_packages' => $disabledPackages,
            'total_users' => $totalUsers,
            'total_qrcodes' => $totalQRCodes,
            'active_qrcodes' => $activeQRCodes,
            'inactive_qrcodes' => $inactiveQRCodes,
        ], 200);
    }

    public function deleteQrCodeAndProfile($qrCodeId)
    {
        
        $qrCode = QrCodeModel::find($qrCodeId);
    

        if (!$qrCode) {
            return response()->json(['message' => 'QR code not found'], 404);
        }
    
    
        DB::beginTransaction();
    
        try {
       
            $qrCode->delete();
    
       
            $profile = $qrCode->profile;
    
            if ($profile) {
          
                $profile->links()->delete();
                $profile->images()->delete();
                $profile->pdfs()->delete();
                $profile->events()->delete();
                $profile->branches()->delete();
    
         
                $profile->delete();
            }
    
         
            DB::commit();
    
            return response()->json(['message' => 'QR code and its profile and related data deleted successfully'], 200);
        } catch (\Exception $e) {
        
            DB::rollBack();
    
            return response()->json(['message' => 'An error occurred while deleting the QR code and related data', 'error' => $e->getMessage()], 500);
        }
    }
    
    

}


