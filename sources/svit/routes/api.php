<?php

use App\Http\Controllers\Api\CsvController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::group(['prefix' => 'csv'], function () {
    Route::post('/convert-sql-to-csv', [CsvController::class, 'createFromSql'])->name('api.csv.createFromSql');
    Route::post('/merge', [CsvController::class, 'merge'])->name('api.csv.merge');
    Route::get('/download/{folderName}/{fileName}', [CsvController::class, 'download'])->name('api.csv.download');
});
