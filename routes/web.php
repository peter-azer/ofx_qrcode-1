<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\QRCodeController;

Route::get('/', function () {
    return view('welcome');
});


Route::get('/qrcode/{name}', [QrCodeController::class, 'trackAndRedirectweb'])->name('qrcode.scan');
