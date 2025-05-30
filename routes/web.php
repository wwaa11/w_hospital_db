<?php
use App\Http\Controllers\CoreController;
use App\Http\Controllers\K2Controller;
use Illuminate\Support\Facades\Route;

Route::get('/', [CoreController::class, 'AppointmentSAP']);

Route::get('/a7z94', [CoreController::class, 'A7z94']);
Route::get('/a7z94Xray', [CoreController::class, 'A7Z94XRAY']);
Route::get('/line', [CoreController::class, 'lineOALog']);
Route::get('/pdpa3', [CoreController::class, 'PDPA3']);
Route::get('/rsv', [CoreController::class, 'RSV']);

Route::get('/depress', [CoreController::class, 'Depress']);

Route::get('/k2/med3', [K2Controller::class, 'MedicalType3']);
Route::get('/k2/procedure_add', [K2Controller::class, 'k2procedure_add']);
Route::get('/k2/procedure_remove', [K2Controller::class, 'k2procedure_remove']);
