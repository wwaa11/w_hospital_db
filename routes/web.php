<?php
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CoreController;
use App\Http\Controllers\K2Controller;
use App\Http\Controllers\QueryController;
use Illuminate\Support\Facades\Route;

Route::get('/test', [CoreController::class, 'lastLabXrayHN'])->name('index');
// QUERY
Route::get('/', [CoreController::class, 'index'])->name('index');
Route::get('/depression', [QueryController::class, 'Depression'])->name('depression.index');
// Dev
Route::get('/appmnt', [CoreController::class, 'AppmntQuery']);
Route::get('/gdpa', [CoreController::class, 'getDoctorPatientAppointment']);
Route::get('/line/all', [CoreController::class, 'line_all'])->name('line.all');
Route::get('/a7z94', [CoreController::class, 'A7z94']);
Route::get('/a7z94Xray', [CoreController::class, 'A7Z94XRAY']);
Route::get('/line', [CoreController::class, 'lineOALog']);
Route::get('/pdpa3', [CoreController::class, 'PDPA3']);
Route::get('/rsv', [CoreController::class, 'RSV']);
Route::get('/arcode', [CoreController::class, 'ARCode']);
// Excel Import Routes
Route::get('/random-excel', [CoreController::class, 'excelImport'])->name('excel.import.page');
Route::post('/random-excel', [CoreController::class, 'processExcelImport'])->name('excel.import');
Route::post('/excel-random', [CoreController::class, 'getRandomRows'])->name('excel.random');

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
