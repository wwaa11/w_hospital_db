<?php
namespace App\Http\Controllers;

use App\Http\Controllers\HelperController;
use DB;
use Illuminate\Http\Request;

class QueryController extends Controller
{
    public function Depression(Request $request)
    {
        $startDate         = $request->input('startdate', date('Y-01-01'));
        $endDate           = $request->input('enddate', date('Y-03-31'));
        $RecoveryStartDate = $request->input('recoverystartdate', date('Y-04-30'));
        $RecoveryEndDate   = $request->input('recoveryenddate', date('Y-06-30'));

        $data        = [];
        $checkFollow = [];
        $doctorName  = DB::connection('SSB')->table("HNDOCTOR_MASTER")->get();
        $clinicName  = DB::connection('SSB')->table("DNSYSCONFIG")->where('CtrlCode', '42203')->get();

        $clinic = ['1500', '1502'];
        $icd    = ['F32.0', 'F32.1', 'F32.2', 'F32.3', 'F32.4', 'F32.6', 'F32.7', 'F32.8', 'F32.9'];
        $visits = DB::connection('SSB')
            ->table('HNOPD_MASTER')
            ->join('HNOPD_PRESCRIP', function ($join) {
                $join->on('HNOPD_MASTER.VisitDate', '=', 'HNOPD_PRESCRIP.VisitDate')
                    ->on('HNOPD_MASTER.VN', '=', 'HNOPD_PRESCRIP.VN');
            })
            ->join('HNOPD_PRESCRIP_DIAG', function ($join) {
                $join->on('HNOPD_MASTER.VisitDate', '=', 'HNOPD_PRESCRIP_DIAG.VisitDate')
                    ->on('HNOPD_MASTER.VN', '=', 'HNOPD_PRESCRIP_DIAG.VN');
            })
            ->join('HNPAT_NAME', function ($join) {
                $join->on('HNOPD_MASTER.HN', '=', 'HNPAT_NAME.HN')
                    ->where('HNPAT_NAME.SuffixSmall', 0);
            })
            ->whereDate('HNOPD_MASTER.VisitDate', '>=', $startDate)
            ->whereDate('HNOPD_MASTER.VisitDate', '<=', $endDate)
            ->where('HNOPD_PRESCRIP.CloseVisitCode', '<>', 99)
            ->whereIn('HNOPD_PRESCRIP.Clinic', $clinic)
            ->whereIn('HNOPD_PRESCRIP.NewToHere', [1, 2])
            ->whereIn('HNOPD_PRESCRIP_DIAG.ICDCode', $icd)
            ->orderBy('HNOPD_MASTER.VisitDate', 'ASC')
            ->get();
        $hnNew = [];
        foreach ($visits as $item) {
            if (! in_array($item->HN, $hnNew)) {
                $hnNew[]     = $item->HN;
                $data['1'][] = [
                    'visit'  => HelperController::SetDate($item->VisitDate, 'd F Y'),
                    'hn'     => $item->HN,
                    'name'   => HelperController::FullName($item->FirstName, $item->LastName),
                    'clinic' => HelperController::ClinicName($clinicName, $item->Clinic),
                    'doctor' => HelperController::DoctorName($doctorName, $item->Doctor),
                ];

                $appointments = DB::connection('SSB')
                    ->table('HNAPPMNT_HEADER')
                    ->leftjoin('HNAPPMNT_LOG', 'HNAPPMNT_HEADER.AppointmentNo', 'HNAPPMNT_LOG.AppointmentNo')
                    ->join('HNPAT_NAME', function ($join) {
                        $join->on('HNAPPMNT_HEADER.HN', '=', 'HNPAT_NAME.HN')
                            ->where('HNPAT_NAME.SuffixSmall', 0);
                    })
                    ->where('HNAPPMNT_HEADER.HN', $item->HN)
                    ->whereDate('HNAPPMNT_HEADER.AppointDateTime', '>=', $item->VisitDate)
                    ->whereDate('HNAPPMNT_HEADER.AppointDateTime', '<=', date('Y-m-d'))
                    ->whereIn('HNAPPMNT_HEADER.Clinic', $clinic)
                    ->whereIn('HNAPPMNT_LOG.HNAppointmentLogType', [1, 4, 12])
                    ->select(
                        'HNAPPMNT_HEADER.AppointDateTime',
                        'HNAPPMNT_HEADER.AppointmentNo',
                        'HNAPPMNT_LOG.HNAppointmentLogType',
                    )
                    ->orderBy('HNAPPMNT_HEADER.AppointDateTime', 'ASC')
                    ->get();

                if (count($appointments) > 0) {
                    foreach ($appointments as $app) {
                        $checkFollow[$item->HN]['name']                             = HelperController::FullName($item->FirstName, $item->LastName);
                        $checkFollow[$item->HN]['app'][$app->AppointmentNo]['date'] = HelperController::SetDate($app->AppointDateTime, 'd F Y');
                        if ($app->HNAppointmentLogType == 12) {
                            $checkFollow[$item->HN]['app'][$app->AppointmentNo]['status'] = 'Attended';
                        } else if ($app->HNAppointmentLogType == 4) {
                            $checkFollow[$item->HN]['app'][$app->AppointmentNo]['status'] = 'Canceled';
                        } else {
                            $checkFollow[$item->HN]['app'][$app->AppointmentNo]['status'] = 'Loss';
                        }
                        $checkFollow[$item->HN]['app'][$app->AppointmentNo]['log'][] = [
                            'log' => $app->HNAppointmentLogType,
                        ];
                    }
                }
            }
        }
        $visits = DB::connection('SSB')
            ->table('HNOPD_MASTER')
            ->join('HNOPD_PRESCRIP', function ($join) {
                $join->on('HNOPD_MASTER.VisitDate', '=', 'HNOPD_PRESCRIP.VisitDate')
                    ->on('HNOPD_MASTER.VN', '=', 'HNOPD_PRESCRIP.VN');
            })
            ->join('HNOPD_PRESCRIP_DIAG', function ($join) {
                $join->on('HNOPD_MASTER.VisitDate', '=', 'HNOPD_PRESCRIP_DIAG.VisitDate')
                    ->on('HNOPD_MASTER.VN', '=', 'HNOPD_PRESCRIP_DIAG.VN');
            })
            ->join('HNPAT_NAME', function ($join) {
                $join->on('HNOPD_MASTER.HN', '=', 'HNPAT_NAME.HN')
                    ->where('HNPAT_NAME.SuffixSmall', 0);
            })
            ->whereDate('HNOPD_MASTER.VisitDate', '>=', $RecoveryStartDate)
            ->whereDate('HNOPD_MASTER.VisitDate', '<=', $RecoveryEndDate)
            ->whereIn('HNOPD_MASTER.HN', $hnNew)
            ->whereIn('HNOPD_PRESCRIP.Clinic', $clinic)
            ->whereIn('HNOPD_PRESCRIP_DIAG.ICDCode', ['F32.5'])
            ->orderBy('HNOPD_MASTER.VisitDate', 'ASC')
            ->get();

        foreach ($visits as $item) {
            $data['2'][] = [
                'visit'  => HelperController::SetDate($item->VisitDate, 'd F Y'),
                'hn'     => $item->HN,
                'name'   => HelperController::FullName($item->FirstName, $item->LastName),
                'clinic' => HelperController::ClinicName($clinicName, $item->Clinic),
                'doctor' => HelperController::DoctorName($doctorName, $item->Doctor),
            ];
        }

        $date = [
            'date_1_form' => HelperController::setFullDate($startDate),
            'date_1_to'   => HelperController::setFullDate($endDate),
            'date_2_form' => HelperController::setFullDate($RecoveryStartDate),
            'date_2_to'   => HelperController::setFullDate($RecoveryEndDate),
        ];

        return view('Query.depression')->with(compact('data', 'date', 'checkFollow'));
    }
}
