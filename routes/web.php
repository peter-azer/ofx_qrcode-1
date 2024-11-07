<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\QRCodeController;
use App\Http\Controllers\Smart_QRCodeController;
Route::get('/', function () {
    return view('welcome');
});


Route::get('/qrcode/{name}', [QrCodeController::class, 'trackAndRedirectweb'])->name('qrcode.scan');


Route::get('/download-qrcode/{fileName}', [Smart_QRCodeController::class, 'downloadQRCode']);
