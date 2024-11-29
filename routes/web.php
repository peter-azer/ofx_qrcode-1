<?php

use App\Http\Controllers\qrcodev2Controller;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\QRCodeController;
use App\Http\Controllers\Smart_QRCodeController;
Route::get('/', function () {
    return view('welcome');
});


Route::get('/qrcode/{name}', [QrCodeController::class, 'trackAndRedirectweb'])->name('qrcode.scan');


Route::get('/download-qrcode/{fileName}', [Smart_QRCodeController::class, 'downloadQRCode_image']);
Route::get('/download-qrcode/pdf/{fileName}', [Smart_QRCodeController::class, 'downloadQRCode_pdf']);

Route::post('/qrcode/smart', [Smart_QRCodeController::class, 'generatesmartQRCodev2']);
Route::get('/generate-qr', [Smart_QRCodeController::class, 'showForm']);

Route::get('/profile', function () {
    return view('profile');
})->name('profile');

Route::post('/save-profile', [qrcodev2Controller::class, 'saveProfileData'])->name('saveProfileData');



Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');




use App\Http\Controllers\PaymentController;

// Route to initiate the payment
Route::post('/payment/initiate', [PaymentController::class, 'initiatePayment'])->name('payment.initiate');

Route::get('/payment/callback', [PaymentController::class, 'paymentCallback'])->name('payment.callback');


// Route::get('/payment-summary', [PaymentController::class, 'paymentSummary'])->name('payment.summary');
