<?php
namespace App\Http\Controllers;

use App\Imports\Med3;
use App\Imports\Procedure;
use DB;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class K2Controller extends Controller
{
    public function index()
    {
        return view('k2.index');
    }

    public function deleteProcedure()
    {
        dump('deleteProcedure');
        dump('Prevent accident delete procedure!');
        die();

        $procedure_id = 695;
        $procedure    = DB::connection('K2DEV_SUR')
            ->table('m_Procedure')
            ->where('ID', '>=', $procedure_id)
            ->delete();

        $procedure_supplies = DB::connection('K2DEV_SUR')
            ->table('m_MedicalSuppliesInOper')
            ->where('ProcedureID', '>=', $procedure_id)
            ->delete();
    }

    public function Procedure(Request $request)
    {
        $clinics = DB::connection('K2DEV_SUR')
            ->table('m_ClinicName')
            ->where('Status', 'Active')
            ->select(
                'ClinicShortName',
                'ClinicNameTH',
            )
            ->get();

        return view('k2.Procedure_upload', compact('clinics'));
    }
    public function uploadProcedureFile(Request $request)
    {
        $request->validate([
            'file'        => 'required|mimes:xlsx,xls',
            'clinic'      => 'required|string',
            'environment' => 'required|in:K2DEV_SUR,K2PROD_SUR',
        ]);

        Excel::import(new Procedure($request->clinic, $request->environment), $request->file);

        return redirect()->back()->with('success', 'File uploaded successfully for clinic: ' . $request->clinic . ' in ' . ($request->environment === 'K2DEV_SUR' ? 'Development' : 'Production') . ' environment');
    }

    public function Med3(Request $request)
    {
        $clinics = DB::connection('K2DEV_SUR')
            ->table('m_ClinicName')
            ->where('Status', 'Active')
            ->select(
                'ClinicShortName',
                'ClinicNameTH',
            )
            ->get();

        return view('k2.Med3_upload', compact('clinics'));
    }

    public function uploadMed3File(Request $request)
    {
        $request->validate([
            'environment' => 'required|in:K2DEV_SUR,K2PROD_SUR',
            'clinics'     => 'required|array|min:1',
            'file'        => 'required|file|mimes:xlsx,xls',
        ]);

        try {
            $file        = $request->file('file');
            $environment = $request->input('environment');
            $clinics     = $request->input('clinics');

            // Process for each selected clinic
            foreach ($clinics as $clinic) {
                Excel::import(new Med3($clinic, $environment), $file);
            }
            $clinicText = count($clinics) === 1 ? $clinics[0] : 'selected clinics';
            return redirect()->back()->with('success', "Med3 data has been successfully uploaded to {$clinicText} in " . ($environment === 'K2DEV_SUR' ? 'Development' : 'Production') . " environment.");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error uploading file: ' . $e->getMessage());
        }
    }

}
