<?php
use App\Http\Controllers\CoreController;
use App\Http\Controllers\K2Controller;
use Illuminate\Support\Facades\Route;

Route::get('/', [CoreController::class, 'index']);
Route::get('/depress', [CoreController::class, 'Depress'])->name('depress');

Route::get('/a7z94', [CoreController::class, 'A7z94']);
Route::get('/a7z94Xray', [CoreController::class, 'A7Z94XRAY']);
Route::get('/line', [CoreController::class, 'lineOALog']);
Route::get('/pdpa3', [CoreController::class, 'PDPA3']);
Route::get('/rsv', [CoreController::class, 'RSV']);
Route::get('/arcode', [CoreController::class, 'ARCode']);

// K2 Routes
Route::get('/k2', [K2Controller::class, 'index']);

Route::get('/k2/deleteProcedure', [K2Controller::class, 'deleteProcedure']);

Route::get('/k2/procedure', [K2Controller::class, 'Procedure']);
Route::post('/k2/procedure', [K2Controller::class, 'uploadProcedureFile']);

Route::get('/k2/med3', [K2Controller::class, 'Med3']);
Route::post('/k2/med3', [K2Controller::class, 'uploadMed3File']);
