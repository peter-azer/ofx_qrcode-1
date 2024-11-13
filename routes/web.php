<?php

use App\Http\Controllers\qrcodev2Controller;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\QRCodeController;
use App\Http\Controllers\Smart_QRCodeController;
Route::get('/', function () {
    return view('welcome');
});


Route::get('/qrcode/{name}', [QrCodeController::class, 'trackAndRedirectweb'])->name('qrcode.scan');


Route::get('/download-qrcode/{fileName}', [Smart_QRCodeController::class, 'downloadQRCode']);


Route::post('/qrcode/smart', [Smart_QRCodeController::class, 'generatesmartQRCodev2']);
Route::get('/generate-qr', [Smart_QRCodeController::class, 'showForm']);

Route::get('/profile', function () {
    return view('profile');
})->name('profile');

Route::post('/save-profile', [qrcodev2Controller::class, 'saveProfileData'])->name('saveProfileData');


use Illuminate\Support\Facades\Auth;

Auth::routes();