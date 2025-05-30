<?php
namespace App\Http\Controllers;

use App\Imports\Med3;
use App\Imports\Procedure;
use DB;
use Maatwebsite\Excel\Facades\Excel;

class K2Controller extends Controller
{
    public function k2procedure_add()
    {
        $file = public_path("procedure/OR_22_04.xlsx");
        $data = Excel::import(new Procedure, $file);
    }
    public function k2procedure_remove()
    {
        dump('CLOSE');
        die();
        $clinic = 'OBS';
        $datas  = DB::connection('K2PROD_SUR')
            ->table('m_Procedure')
            ->join('m_MedicalSuppliesInOper', 'm_Procedure.ID', 'm_MedicalSuppliesInOper.ProcedureID')
            ->where('m_Procedure.ClinicShortName', $clinic)
            ->select(
                'm_Procedure.ID as procedureID',
                'm_Procedure.Status as procedureStatus',
                'm_Procedure.ProcedureName as Name',
                'm_MedicalSuppliesInOper.ID as medicalprocedureID',
                'm_MedicalSuppliesInOper.Status as medicalprocedureStatus',
            )
            ->get();

        foreach ($datas as $procedure) {
            $Update_procedure = DB::connection('K2PROD_SUR')
                ->table('m_Procedure')
                ->where('ID', $procedure->procedureID)
            // ->first();
                ->update([
                    'UpdateBy' => 'PAKAWA KAPHONDEE',
                    'Status'   => 'Inactive',
                ]);
            dump($Update_procedure);
            $Update_medprocedure = DB::connection('K2PROD_SUR')
                ->table('m_MedicalSuppliesInOper')
                ->where('ID', $procedure->medicalprocedureID)
            // ->first();
                ->update([
                    'UpdateBy' => 'PAKAWA KAPHONDEE',
                    'Status'   => 'Inactive',
                ]);
            dump($Update_medprocedure);
        }
    }
    public function MedicalType3()
    {
        $files       = \File::glob(public_path('K2_Med3_wait/*.xlsx'));
        $successPath = public_path('K2_Med3_success/');

        foreach ($files as $file) {
            Excel::import(new Med3, $file);
            $fileName = basename($file);
            \File::move($file, $successPath . $fileName);
            dump($file);
        }
    }
}
