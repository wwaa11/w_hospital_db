<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CoreController;
use App\Http\Controllers\K2Controller;
use App\Http\Controllers\QueryController;
use Illuminate\Support\Facades\Route;

Route::get('/', [CoreController::class, 'index'])->name('index');
// QUERY
Route::get('/depression', [QueryController::class, 'Depression'])->name('depression.index');
Route::get('/apppercent', [QueryController::class, 'appPercentOnline'])->name('apppercent.index');
Route::get('/newpercent', [QueryController::class, 'newPatientOnline'])->name('newpercent.index');

// K2 Datamanagement
Route::get('/login', [AuthController::class, 'login'])->name('login');
Route::post('/login', [AuthController::class, 'LoginRequest'])->name('login.post');
Route::get('/logout', [AuthController::class, 'LogoutRequest'])->name('logout');

Route::group(['middleware' => ['auth']], function () {
    Route::get('/k2', [K2Controller::class, 'index'])->name('k2.index');

    Route::get('/k2/procedure', [K2Controller::class, 'Procedure'])->name('k2.procedure');
    Route::post('/k2/procedure', [K2Controller::class, 'uploadProcedureFile'])->name('k2.uploadProcedureFile');

    Route::get('/k2/med3', [K2Controller::class, 'Med3'])->name('k2.med3');
    Route::post('/k2/med3', [K2Controller::class, 'uploadMed3File'])->name('k2.uploadMed3File');
    Route::get('/k2/med3/deactivate', [K2Controller::class, 'Med3Deactivate'])->name('k2.med3.deactivate');
    Route::post('/k2/med3/deactivate', [K2Controller::class, 'Med3DeactivateUpload'])->name('k2.uploadMed3DeactivateFile');

    Route::get('/k2/delete/procedure', [K2Controller::class, 'deleteProcedure'])->name('k2.deleteProcedure');
    Route::get('/k2/delete/med3', [K2Controller::class, 'deleteMed3'])->name('k2.deleteMed3');
});
