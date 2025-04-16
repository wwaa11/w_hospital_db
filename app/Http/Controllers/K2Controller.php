<?php
namespace App\Http\Controllers;

use DB;
use Maatwebsite\Excel\Facades\Excel;

class K2Controller extends Controller
{
    public function k2procedure_add()
    {
        $file = public_path("procedure/OR_10-04.xlsx");
        $data = Excel::import(new Procedure, $file);
    }
    public function k2procedure_remove()
    {
        die();
        $clinic = 'OBS';
        $datas  = DB::connection('K2DEV_SUR')
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
            $Update_procedure = DB::connection('K2DEV_SUR')
                ->table('m_Procedure')
                ->where('ID', $procedure->procedureID)
                ->update([
                    'UpdateBy' => 'PAKAWA KAPHONDEE',
                    'Status'   => 'Inactive',
                ]);
            $Update_medprocedure = DB::connection('K2DEV_SUR')
                ->table('m_MedicalSuppliesInOper')
                ->where('ID', $procedure->medicalprocedureID)
                ->update([
                    'UpdateBy' => 'PAKAWA KAPHONDEE',
                    'Status'   => 'Inactive',
                ]);
        }
    }
    public function k2suplile3()
    {
        $file = public_path("excel/GRAYTINGAY.xlsx");
        $data = Excel::import(new ExcelImport, $file);
    }
}
