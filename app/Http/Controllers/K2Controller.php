<?php
namespace App\Http\Controllers;

use App\Imports\Med3;
use App\Imports\Med3Deactivate;
use App\Imports\Procedure;
use App\Imports\Equipment;
use App\Imports\EquipmentDeactivate;
use App\Imports\Anesthesia;
use DB;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class K2Controller extends Controller
{
    public function index()
    {
        return redirect()->route('k2.procedure');
    }


    public function deleteProcedure()
    {
        dump('Inactive Procedure');
        $server = 'K2PROD_SUR';
        // $server = 'K2DEV_SUR';
        die();
        $count = DB::connection($server)->table('m_Procedure')->update([
            'Status' => 'Inactive',
        ]);
        DB::connection($server)->table('m_MedicalSuppliesInOper')->update([
            'Status' => 'Inactive',
        ]);
        dump('Inactive Procedure Success ' . $count . ' rows updated');
    }

    public function deleteMed3()
    {
        dump('Inactive Med3');
        $server = 'K2PROD_SUR';
        // $server = 'K2DEV_SUR';
        die();

        $count = DB::connection($server)->table('m_MedicalSupplies3')->update([
            'Status' => 'Inactive',
        ]);
        dump('Inactive Med3 Success ' . $count . ' rows updated');
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
            dd($e->getMessage());

            return redirect()->back()->with('error', 'Error uploading file: ' . $e->getMessage());
        }
    }

    public function Med3Deactivate(Request $request)
    {
        $clinics = DB::connection('K2DEV_SUR')
            ->table('m_ClinicName')
            ->where('Status', 'Active')
            ->select(
                'ClinicShortName',
                'ClinicNameTH',
            )
            ->get();

        return view('k2.Med3_deactivate', compact('clinics'));
    }

    public function Med3DeactivateUpload(Request $request)
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
                Excel::import(new Med3Deactivate($clinic, $environment), $file);
            }
            $clinicText = count($clinics) === 1 ? $clinics[0] : 'selected clinics';

            return redirect()->back()->with('success', "Med3 data has been successfully uploaded to {$clinicText} in " . ($environment === 'K2DEV_SUR' ? 'Development' : 'Production') . " environment.");
        } catch (\Exception $e) {
            dd($e->getMessage());

            return redirect()->back()->with('error', 'Error uploading file: ' . $e->getMessage());
        }
    }

    public function Equipment(Request $request)
    {
        $clinics = DB::connection('K2DEV_SUR')
            ->table('m_ClinicName')
            ->where('Status', 'Active')
            ->select(
                'ClinicShortName',
                'ClinicNameTH',
            )
            ->get();

        return view('k2.Equipment_upload', compact('clinics'));
    }

    public function uploadEquipmentFile(Request $request)
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
                Excel::import(new Equipment($clinic, $environment), $file);
            }
            $clinicText = count($clinics) === 1 ? $clinics[0] : 'selected clinics';

            return redirect()->back()->with('success', "Equipment data has been successfully uploaded to {$clinicText} in " . ($environment === 'K2DEV_SUR' ? 'Development' : 'Production') . " environment.");
        } catch (\Exception $e) {
            dd($e->getMessage());

            return redirect()->back()->with('error', 'Error uploading file: ' . $e->getMessage());
        }
    }

    public function EquipmentDeactivate(Request $request)
    {
        $clinics = DB::connection('K2DEV_SUR')
            ->table('m_ClinicName')
            ->where('Status', 'Active')
            ->select(
                'ClinicShortName',
                'ClinicNameTH',
            )
            ->get();

        return view('k2.Equipment_deactivate', compact('clinics'));
    }

    public function EquipmentDeactivateUpload(Request $request)
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
                Excel::import(new EquipmentDeactivate($clinic, $environment), $file);
            }
            $clinicText = count($clinics) === 1 ? $clinics[0] : 'selected clinics';

            return redirect()->back()->with('success', "Med3 data has been successfully uploaded to {$clinicText} in " . ($environment === 'K2DEV_SUR' ? 'Development' : 'Production') . " environment.");
        } catch (\Exception $e) {
            dd($e->getMessage());

            return redirect()->back()->with('error', 'Error uploading file: ' . $e->getMessage());
        }
    }

    public function Anesthesia(Request $request)
    {

        return view('k2.Anesthesia_upload');
    }

    public function uploadAnesthesiaFile(Request $request)
    {
        $request->validate([
            'environment' => 'required|in:K2DEV_SUR,K2PROD_SUR',
            'file'        => 'required|file|mimes:xlsx,xls',
        ]);
        try {
            $file        = $request->file('file');
            $environment = $request->input('environment');

            Excel::import(new Anesthesia($environment), $file);

            return redirect()->back()->with('success', "Anesthesia data has been successfully uploaded in " . ($environment === 'K2DEV_SUR' ? 'Development' : 'Production') . " environment.");
        } catch (\Exception $e) {
            dd($e->getMessage());

            return redirect()->back()->with('error', 'Error uploading file: ' . $e->getMessage());
        }
    }
}
