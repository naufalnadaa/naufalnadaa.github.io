<?php

use App\Http\Controllers\KartuAirSehatController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/index', [KartuAirSehatController::class, 'emailPage'])->name('email-page');
Route::get('/reload-captcha', [KartuAirSehatController::class, 'reloadCaptcha'])->name('reload-captcha');
Route::post('/validate-email', [KartuAirSehatController::class, 'validateEmail'])->name('validate-email');
Route::get('/customer-check', [KartuAirSehatController::class, 'cekDataKonsumen'])->name('check-data-konsumen');
Route::post('/search-customer-data', [KartuAirSehatController::class, 'searchDataPelanggan'])->name('search-customer-data');
Route::get('/customer-check-data', [KartuAirSehatController::class, 'cekDataKonsumenDB'])->name('check-data-konsumen-db');
Route::get('/customer-page', [KartuAirSehatController::class, 'customerPage'])->name('customer-page');
Route::get('/download-page', [KartuAirSehatController::class, 'downloadPage'])->name('download-page');
Route::post('/file-download', [KartuAirSehatController::class, 'fileDownload'])->name('file-download');
