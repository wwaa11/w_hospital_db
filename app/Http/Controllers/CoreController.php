<?php
namespace App\Http\Controllers;

use App\Http\Controllers\HelperController;
use Carbon\Carbon;
use DB;
use File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CoreController extends Controller
{
    public function __construct()
    {
        mb_internal_encoding('UTF-8');
    }
    public function index()
    {

        return view('layouts.index');
    }

    public function generateMonthlyDateBatches(string $startDate, string $endDate): array
    {
        // Convert strings to Carbon objects for easy manipulation
        $start   = Carbon::parse($startDate)->startOfMonth();
        $end     = Carbon::parse($endDate);
        $batches = [];
        // Loop while the current month is before or the same as the target end month
        while ($start->lessThanOrEqualTo($end)) {
            $currentMonth = $start->clone();
            // Start date is the 1st of the current month
            $startOfMonth = $currentMonth->format('Y-m-d');
            // End date is the last day of the current month
            $endOfMonth = $currentMonth->endOfMonth()->format('Y-m-d');
            $batches[]  = [
                $startOfMonth,
                $endOfMonth,
            ];
            // Move the pointer to the 1st day of the next month for the next iteration
            $start->addMonth()->startOfMonth();
        }

        return $batches;
    }

    public function appPercentOnline()
    {
        $startDate      = '2022-11-01';
        $endDate        = '2025-12-31';
        $monthlyBatches = $this->generateMonthlyDateBatches($startDate, $endDate);

        $data = [];
        foreach ($monthlyBatches as $b) {
            $start = $b[0];
            $end   = $b[1];

            $apps = DB::connection('SSB')
                ->table("HNAPPMNT_HEADER")
                ->leftJoin('HNAPPMNT_LOG', 'HNAPPMNT_HEADER.AppointmentNo', 'HNAPPMNT_LOG.AppointmentNo')
                ->whereNull('CxlReasonCode')
                ->where('AppointDateTime', '>=', $start)
                ->where('AppointDateTime', '<=', $end)
                ->orderBy('HNAPPMNT_HEADER.AppointDateTime', 'ASC')
                ->whereIn('HNAPPMNT_LOG.HNAppointmentLogType', ['1', '12'])
                ->select(
                    'HNAPPMNT_HEADER.HN',
                    'HNAPPMNT_HEADER.AppointmentNo',
                    'HNAPPMNT_HEADER.AppointDateTime as apptDateTime',
                    'HNAPPMNT_LOG.HNAppointmentLogType'
                )
                ->get();

            $data[$start] = [
                'ssb'           => 0,
                'ssb-create'    => 0,
                'ssb-attend'    => 0,
                'online'        => 0,
                'online-create' => 0,
                'online-attend' => 0,
            ];
            $countAPP = [];
            foreach ($apps as $app) {
                $type = (substr($app->AppointmentNo, 0, 2) == 'AP') ? 'ssb' : 'online';

                if (! in_array($app->AppointmentNo, $countAPP)) {
                    $countAPP[] = $app->AppointmentNo;
                    $data[$start][$type]++;
                }

                if ($app->HNAppointmentLogType == '1') {
                    $data[$start][$type . '-create']++;
                } else {
                    $data[$start][$type . '-attend']++;
                }
            }

            foreach ($data as $date => &$metrics) {
                $total                            = $metrics['ssb'] + $metrics['online'];
                $metrics['total']                 = $total;
                $metrics['ssb-percent']           = $total > 0 ? number_format(($metrics['ssb'] / $total) * 100, 2) . '%' : 'N/A (Div by zero)';
                $metrics['ssb-miss']              = $metrics['ssb'] - $metrics['ssb-attend'];
                $metrics['ssb-miss-percent']      = $total > 0 ? number_format(($metrics['ssb-miss'] / $total) * 100, 2) . '%' : 'N/A (Div by zero)';
                $metrics['online-percent']        = $total > 0 ? number_format(($metrics['online'] / $total) * 100, 2) . '%' : 'N/A (Div by zero)';
                $metrics['online-miss']           = $metrics['online'] - $metrics['online-attend'];
                $metrics['online-miss-percent']   = $total > 0 ? number_format(($metrics['online-miss'] / $total) * 100, 2) . '%' : 'N/A (Div by zero)';
                $metrics['ssb-attend-percent']    = $total > 0 ? number_format(($metrics['ssb-attend'] / $total) * 100, 2) . '%' : 'N/A (Div by zero)';
                $metrics['online-attend-percent'] = $total > 0 ? number_format(($metrics['online-attend'] / $total) * 100, 2) . '%' : 'N/A (Div by zero)';
            }
        }

        return view('Query.appointment-percentage', compact('data'));
    }

    public function lastLabXrayHN()
    {
        $labHeader = DB::connection('SSB')->table("HNLABREQ_HEADER")
        // ->join('HNLABREQ_RESULT', 'HNLABREQ_HEADER.RequestNo', 'HNLABREQ_RESULT.RequestNo')
        // ->where('HN', 'like', '%-%')
            ->whereRaw('LEN(HN) = 7')->whereRaw("HN NOT LIKE '%[^0-9]%'")

        // ->where('HN', '867848')
            ->whereNull('CxlDateTime')
            ->select(
                'HNLABREQ_HEADER.HN',
                'HNLABREQ_HEADER.RequestNo as labRequestNo',
                'HNLABREQ_HEADER.EntryDateTime as labEntryDateTime',
                'HNLABREQ_HEADER.FacilityRmsNo as labFacilityRmsNo',
                DB::raw("'lab' as type")
            )
            ->orderBy('HNLABREQ_HEADER.HN', 'ASC')
            ->orderBy('HNLABREQ_HEADER.EntryDateTime', 'ASC')
            ->get();
        $xrayHeader = DB::connection('SSB')->table("HNXRAYREQ_HEADER")
        // ->join('HNXRAYREQ_RESULT', 'HNXRAYREQ_HEADER.RequestNo', 'HNXRAYREQ_RESULT.RequestNo')
        // ->where('HN', 'like', '%-%')
            ->whereRaw('LEN(HN) = 7')->whereRaw("HN NOT LIKE '%[^0-9]%'")
        // ->where('HN', '867848')
            ->whereNull('CxlDateTime')
            ->select(
                'HNXRAYREQ_HEADER.HN',
                'HNXRAYREQ_HEADER.RequestNo as xrayRequestNo',
                'HNXRAYREQ_HEADER.EntryDateTime as xrayEntryDateTime',
                'HNXRAYREQ_HEADER.FacilityRmsNo as xrayFacilityRmsNo',
                DB::raw("'xray' as type")
            )
            ->orderBy('HNXRAYREQ_HEADER.HN', 'ASC')
            ->orderBy('HNXRAYREQ_HEADER.EntryDateTime', 'ASC')
            ->get();

        $alldata = $labHeader->merge($xrayHeader);
        $datas   = [];
        foreach ($alldata as $item) {
            if (! isset($datas[$item->HN])) {
                $datas[$item->HN] = [
                    'HN'                => $item->HN,
                    'labRequestNo'      => null,
                    'labEntryDateTime'  => null,
                    'labFacilityRmsNo'  => null,
                    'xrayRequestNo'     => null,
                    'xrayEntryDateTime' => null,
                    'xrayFacilityRmsNo' => null,
                ];
            }
            if ($item->type == 'lab') {
                $datas[$item->HN]['labRequestNo']     = $item->labRequestNo;
                $datas[$item->HN]['labEntryDateTime'] = $item->labEntryDateTime;
                $datas[$item->HN]['labFacilityRmsNo'] = $item->labFacilityRmsNo;
            }
            if ($item->type == 'xray') {
                $datas[$item->HN]['xrayRequestNo']     = $item->xrayRequestNo;
                $datas[$item->HN]['xrayEntryDateTime'] = $item->xrayEntryDateTime;
                $datas[$item->HN]['xrayFacilityRmsNo'] = $item->xrayFacilityRmsNo;
            }
        }

        return view('lastLABXRAY', compact('datas'));
    }

    public function AppmntQuery()
    {
        $data = DB::connection('SSB')->table("HNAPPMNT_HEADER")
            ->leftJoin('HNAPPMNT_MSG', 'HNAPPMNT_HEADER.AppointmentNo', 'HNAPPMNT_MSG.AppointmentNo')
            ->whereDate('HNAPPMNT_HEADER.AppointDateTime', '>=', '2025-11-01')
            ->whereDate('HNAPPMNT_HEADER.AppointDateTime', '<=', '2026-01-31')
            ->whereNull('CxlReasonCode')
            ->where('HNAPPMNT_HEADER.HN', 'like', '%-%')
            ->select(
                'HNAPPMNT_HEADER.AppointmentNo',
                'HNAPPMNT_HEADER.HN',
                'HNAPPMNT_HEADER.Doctor',
                'HNAPPMNT_HEADER.Clinic',
                'HNAPPMNT_HEADER.MobilePhone',
                'HNAPPMNT_HEADER.AppointDateTime',
                'HNAPPMNT_HEADER.LastHNAppointmentLogType',
                'HNAPPMNT_MSG.HNAppointmentMsgType',
            )
            ->orderBy('HNAPPMNT_HEADER.HN', 'ASC')
            ->orderBy('HNAPPMNT_HEADER.AppointDateTime', 'ASC')
            ->get();

        $hns   = $data->pluck('HN')->unique();
        $names = DB::connection('SSB')->table("HNPAT_NAME")
            ->whereIn('HN', $hns)
            ->select(
                'HN',
                'InitialNameCode',
                'FirstName',
                'LastName',
            )
            ->where('SuffixSmall', 0)
            ->get();

        $initials = DB::connection('SSB')->table("DNSYSCONFIG")
            ->where('CtrlCode', '10241')
            ->get();

        foreach ($initials as $item) {
            $initialsArray[$item->Code] = mb_substr($item->LocalName, 1);
        }

        $addAPP    = [];
        $dataArray = [];

        foreach ($data as $item) {
            if (! in_array($item->AppointmentNo, $addAPP)) {
                $name    = $names->where('HN', $item->HN)->first();
                $dataArr = [
                    'AppointmentNo'            => $item->AppointmentNo,
                    'HN'                       => $item->HN,
                    'Initials'                 => ($name->InitialNameCode ? $initialsArray[$name->InitialNameCode] : null),
                    'FirstName'                => mb_substr($name->FirstName, 1),
                    'LastName'                 => mb_substr($name->LastName, 1),
                    'Doctor'                   => $item->Doctor,
                    'Clinic'                   => $item->Clinic,
                    'MobilePhone'              => $item->MobilePhone,
                    'AppointDateTime'          => $item->AppointDateTime,
                    'LastHNAppointmentLogType' => $item->LastHNAppointmentLogType,
                    'HNAppointmentMsgType'     => null,
                ];
                $addAPP[]                        = $item->AppointmentNo;
                $dataArray[$item->AppointmentNo] = $dataArr;
            }

            if ($item->HNAppointmentMsgType == '3') {
                $dataArray[$item->AppointmentNo]['HNAppointmentMsgType'] = 3;
            }
        }

        return view('appmnt')->with(compact('dataArray'));
    }

    public function getDoctorPatientAppointment()
    {
        $apps = DB::connection('SSB')->table("HNAPPMNT_HEADER")
            ->whereDate('AppointDateTime', '2025-08-24')
            ->leftjoin('HNPAT_NAME', 'HNAPPMNT_HEADER.HN', 'HNPAT_NAME.HN')
            ->where('HNPAT_NAME.SuffixSmall', 0)
            ->whereNull('CxlReasonCode')
            ->select(
                'HNAPPMNT_HEADER.HN',
                'HNAPPMNT_HEADER.AppointmentNo',
                'HNAPPMNT_HEADER.AppointDateTime as date',
                'HNAPPMNT_HEADER.Doctor as doctor',
                'HNAPPMNT_HEADER.Clinic as clinic',
                'HNPAT_NAME.FirstName',
                'HNPAT_NAME.LastName',
            )
            ->orderBy('HNAPPMNT_HEADER.HN', 'ASC')
            ->orderBy('HNAPPMNT_HEADER.Clinic', 'ASC')
            ->get();
        $doctorName = DB::connection('SSB')->table("HNDOCTOR_MASTER")->get();
        $clinicName = DB::connection('SSB')->table("DNSYSCONFIG")->where('CtrlCode', '42203')->get();

        $data = [];
        foreach ($apps as $app) {
            if (! array_key_exists($app->HN, $data)) {
                $data[$app->HN] = [
                    'hn'     => $app->HN,
                    'appno'  => $app->AppointmentNo,
                    'date'   => $app->date,
                    'name'   => HelperController::FullName($app->FirstName, $app->LastName),
                    'clinic' => [],
                    'count'  => 0,
                ];
            }
            $data[$app->HN]['count'] += 1;
            $data[$app->HN]['clinic'][] = [
                'clinic' => HelperController::ClinicName($clinicName, $app->clinic),
                'doctor' => HelperController::DoctorName($doctorName, $app->doctor),
            ];
        }

        return view('gdpa')->with(compact('apps', 'data'));
    }

    public function line_all()
    {
        $output = DB::connection("SSB")
            ->table("HNPAT_REF")
            ->join("HNPAT_INFO", "HNPAT_REF.HN", "=", "HNPAT_INFO.HN")
            ->whereNotNull('LineID')
            ->select(
                'HNPAT_INFO.LineID',
                'HNPAT_INFO.HN',
                'HNPAT_REF.RefNoType',
                'HNPAT_REF.IDCardType',
                'HNPAT_REF.RefNo',
                'HNPAT_REF.RefIssueBy',
            )
            ->orderBy('HNPAT_INFO.HN', 'desc')
            ->get();

        return view('lineRef')->with(compact('output'));
    }

    public function AppointmentSAP()
    {
        $apps = DB::connection('SSB')->table("HNAPPMNT_HEADER")
            ->where('AppointmentNo', 'Like', 'SAP68-%')
            ->whereNull('CxlReasonCode')
            ->select(
                'AppointmentNo',
                'HN',
                'AppointDateTime'
            )
            ->get();
        foreach ($apps as $app) {
            $app->date = date('Y-m-d', strtotime($app->AppointDateTime));
        }

        return view('app')->with(compact('apps'));
    }
    public function RSV(Request $request)
    {
        $dateQuery = [
            'start' => date('Y-m-d', strtotime('-2 years', strtotime(date('Y-m-d')))),
        ];
        $req = $request->query;

        $doctorName = DB::connection('SSB')->table("HNDOCTOR_MASTER")->get();
        $data       = DB::connection('SSB')
            ->table('HNPAT_INFO')
        // ->join('HNOPD_PRESCRIP', function ($join) {
        //     $join->on('HNOPD_MASTER.VisitDate', '=', 'HNOPD_PRESCRIP.VisitDate');
        //     $join->on('HNOPD_MASTER.VN', '=', 'HNOPD_PRESCRIP.VN');
        // })
            ->leftjoin('HNPAT_NAME', 'HNPAT_INFO.HN', '=', 'HNPAT_NAME.HN')
            ->leftjoin('HNPAT_ADDRESS', 'HNPAT_INFO.HN', '=', 'HNPAT_ADDRESS.HN')
        // ->whereDate('HNOPD_MASTER.Visitdate','>=', $dateQuery['start'])
        // ->whereDate('HNOPD_MASTER.Visitdate','<=', $dateQuery['end'])
            ->where('HNPAT_INFO.BirthDateTime', '>=', $dateQuery['start'])
            ->where('HNPAT_NAME.SuffixSmall', 0)
            ->where('HNPAT_ADDRESS.SuffixTiny', 1)
            ->orderBy('HNPAT_INFO.BirthDateTime', 'ASC')
            ->select(
                // 'HNOPD_MASTER.VN',
                'HNPAT_INFO.HN',
                // 'HNOPD_PRESCRIP.Doctor',
                'HNPAT_NAME.FirstName as name',
                'HNPAT_NAME.LastName as lastname',
                'HNPAT_INFO.BirthDateTime',
                'HNPAT_ADDRESS.MobilePhone',
            )
            ->get();
        $dataOutput = [];
        $hnArr      = [];
        foreach ($data as $hn) {
            $age = HelperController::setAgeInterger($hn->BirthDateTime);
            if ($age <= 2) {
                if (! in_array($hn->HN, $hnArr)) {
                    $hnArr[]             = $hn->HN;
                    $dataOutput[$hn->HN] = [
                        'show'      => true,
                        'diag'      => 'not Found',
                        'vaccine'   => 'not Found',
                        'hn'        => $hn->HN,
                        'name'      => HelperController::FullName($hn->name, $hn->lastname),
                        'age'       => HelperController::setAge($hn->BirthDateTime),
                        'phone'     => $hn->MobilePhone,
                        'lastvisit' => null,
                    ];
                }
            }
        }

        $lastvisits = DB::connection('SSB')
            ->table('HNOPD_MASTER')
            ->join('HNOPD_PRESCRIP', function ($join) {
                $join->on('HNOPD_MASTER.VisitDate', '=', 'HNOPD_PRESCRIP.VisitDate');
                $join->on('HNOPD_MASTER.VN', '=', 'HNOPD_PRESCRIP.VN');
            })
            ->whereIn('HNOPD_MASTER.HN', $hnArr)
            ->orderBy('HNOPD_MASTER.VisitDate', 'DESC')
            ->select(
                'HNOPD_MASTER.HN',
                'HNOPD_MASTER.VisitDate',
                'HNOPD_PRESCRIP.Doctor',
            )
            ->get();

        $icdCode = ["J12.1", "J20.5", "J21.0", "J97.4"];
        $diags   = DB::connection('SSB')
            ->table('HNOPD_MASTER')
            ->join('HNOPD_PRESCRIP_DIAG', function ($join) {
                $join->on('HNOPD_MASTER.VisitDate', '=', 'HNOPD_PRESCRIP_DIAG.VisitDate');
                $join->on('HNOPD_MASTER.VN', '=', 'HNOPD_PRESCRIP_DIAG.VN');
            })
            ->whereIn('HNOPD_MASTER.HN', $hnArr)
            ->whereIn('HNOPD_PRESCRIP_DIAG.ICDCode', $icdCode)
            ->select(
                'HNOPD_MASTER.Visitdate',
                'HNOPD_MASTER.VN',
                'HNOPD_MASTER.HN',
                'HNOPD_PRESCRIP_DIAG.ICDCode as icd',
            )
            ->orderBy('HNOPD_MASTER.VisitDate', 'DESC')
            ->get();
        foreach ($diags as $dia) {
            $dataOutput[$dia->HN]['diag'] = 'found';
        }
        $vaccineStock = ['MYS004S'];
        $vaccine      = DB::connection('SSB')
            ->table('HNOPD_MASTER')
            ->whereIN('HNOPD_MASTER.HN', $hnArr)
            ->leftjoin('HNOPD_PRESCRIP_MEDICINE', function ($join) {
                $join->on('HNOPD_MASTER.VisitDate', '=', 'HNOPD_PRESCRIP_MEDICINE.VisitDate')
                    ->on('HNOPD_MASTER.VN', '=', 'HNOPD_PRESCRIP_MEDICINE.VN');
            })
            ->leftjoin('HNIPD_CHARGE_MEDICINE', 'HNOPD_MASTER.AN', '=', 'HNIPD_CHARGE_MEDICINE.AN')
            ->where(function ($query) use ($vaccineStock) {
                $query->whereIn('HNOPD_PRESCRIP_MEDICINE.StockCode', $vaccineStock)
                    ->orwhereIN('HNIPD_CHARGE_MEDICINE.StockCode', $vaccineStock);
            })
            ->select(
                'HNOPD_MASTER.HN',
                'HNOPD_MASTER.VisitDate',
                'HNOPD_MASTER.VN',
                'HNOPD_MASTER.AN',
                'HNIPD_CHARGE_MEDICINE.IPDChargeDateTime'
            )
            ->orderBy('HNOPD_MASTER.VisitDate', 'ASC')
            ->get();
        foreach ($vaccine as $vac) {
            $dataOutput[$dia->HN]['vaccine'] = 'found';
        }

        foreach ($dataOutput as $out) {
            if ($out['diag'] !== 'found' || $out['vaccine'] !== 'found') {
                $dataOutput[$out['hn']]['show'] == false;
            }
            if ($dataOutput[$out['hn']]['show']) {
                $visitData = collect($lastvisits)->where('HN', $out['hn'])->first();
                if ($visitData !== null) {
                    $dataOutput[$out['hn']]['lastvisit'] = HelperController::DoctorName($doctorName, $visitData->Doctor);
                } else {
                    $dataOutput[$out['hn']]['lastvisit'] = 'ไม่พบข้อมูล Visit';
                }
            }
        }

        return view('rsv')->with(compact('dataOutput', 'dateQuery'));
    }
    public function hospitalrefer()
    {
        $data = DB::connection('SSB')
            ->table('HNOPD_MASTER')
            ->join('HNOPD_PRESCRIP', function ($join) {
                $join->on('HNOPD_MASTER.VisitDate', '=', 'HNOPD_PRESCRIP.VisitDate');
                $join->on('HNOPD_MASTER.VN', '=', 'HNOPD_PRESCRIP.VN');
            })
            ->leftjoin('HNPAT_NAME', 'HNOPD_MASTER.HN', '=', 'HNPAT_NAME.HN')
            ->whereNotNull('HNOPD_MASTER.RefToHospital')
            ->where('HNOPD_MASTER.RefToHospital', '!=', '')
            ->where('HNPAT_NAME.SuffixSmall', 0)
        // ->where('HNOPD_PRESCRIP.Clinic', '1600')
            ->select(
                'HNOPD_MASTER.Visitdate',
                'HNOPD_MASTER.VN',
                'HNOPD_MASTER.HN',
                'HNPAT_NAME.Firstname',
                'HNPAT_NAME.Lastname',
                'HNOPD_PRESCRIP.Clinic',
                'HNOPD_MASTER.RefToHospital',
                'HNOPD_PRESCRIP.CloseVisitCode',
            )
        // ->orderBy('HNOPD_MASTER.RefToHospital', 'ASC')
            ->orderBy('HNOPD_MASTER.Visitdate', 'DESC')
            ->get();

        foreach ($data as $key => $value) {
            $value->fullname = HelperController::FullName($value->Firstname, $value->Lastname);
        }

        return view('hospitalrefer')->with(compact('data'));
    }
    public function A7z94(Request $request)
    {
        ## Logtype
        # 1. Add
        # 4. Cancel
        # 12. Attend

        $dateQuery = [
            'start'  => date('Y-m-d'),
            'end'    => date('Y-m-d'),
            'cancel' => 'false',
        ];

        $req = $request->query;
        foreach ($req as $in => $value) {
            if ($in == 'start') {
                $dateQuery['start'] = $value;
            }
            if ($in == 'end') {
                $dateQuery['end'] = $value;
            }
            if ($in == 'cancel') {
                $dateQuery['cancel'] = $value;
            }
        }
        $findHN = DB::connection('SSB')
            ->table("HNAPPMNT_HEADER")
            ->leftjoin('HNAPPMNT_LOG', 'HNAPPMNT_HEADER.AppointmentNo', 'HNAPPMNT_LOG.AppointmentNo')
            ->whereDate('HNAPPMNT_HEADER.AppointDateTime', '>=', $dateQuery['start'])
            ->whereDate('HNAPPMNT_HEADER.AppointDateTime', '<=', $dateQuery['end'])
            ->whereIn('HNAPPMNT_LOG.HNAppointmentLogType', [1, 4, 12])
            ->where(function ($query) {
                $query->where('HNAPPMNT_HEADER.AppmntProcedureCode1', 'A7')
                    ->orwhere('HNAPPMNT_HEADER.AppmntProcedureCode2', 'A7')
                    ->orwhere('HNAPPMNT_HEADER.AppmntProcedureCode3', 'A7')
                    ->orwhere('HNAPPMNT_HEADER.AppmntProcedureCode4', 'A7')
                    ->orwhere('HNAPPMNT_HEADER.AppmntProcedureCode5', 'A7');
            })
            ->orderBy('HNAPPMNT_HEADER.AppointDateTime', 'ASC')
            ->select(
                'HNAPPMNT_HEADER.HN as hn',
                'HNAPPMNT_HEADER.AppointDateTime as date',
                'HNAPPMNT_HEADER.AppointmentNo as appno',
                'HNAPPMNT_LOG.HNAppointmentLogType as status',
            )
            ->get();

        $hnArray    = [];
        $outputData = [];
        foreach ($findHN as $dataHN) {
            if (! in_array($dataHN->hn, $hnArray)) {
                $hnArray[] = $dataHN->hn;
            }
            if (! array_key_exists($dataHN->hn, $outputData)) {
                $outputData[$dataHN->hn]['show'] = false;
            }
            $outputData[$dataHN->hn]['appointment'][$dataHN->appno]['date']     = $dataHN->date;
            $outputData[$dataHN->hn]['appointment'][$dataHN->appno]['status'][] = $dataHN->status;

        }

        $visits = DB::connection('SSB')
            ->table("HNOPD_MASTER")
            ->leftjoin("HNPAT_NAME", 'HNOPD_MASTER.HN', '=', 'HNPAT_NAME.HN')
            ->join("HNOPD_PRESCRIP_DIAG", function ($join) {
                $join->on('HNOPD_MASTER.VisitDate', '=', 'HNOPD_PRESCRIP_DIAG.VisitDate');
                $join->on('HNOPD_MASTER.VN', '=', 'HNOPD_PRESCRIP_DIAG.VN');
            })
            ->whereIn('HNOPD_MASTER.HN', $hnArray)
            ->where('HNOPD_PRESCRIP_DIAG.ICDCode', 'z94.0')
            ->where('HNPAT_NAME.SuffixSmall', 0)
            ->select(
                'HNOPD_MASTER.VisitDate as date',
                'HNOPD_MASTER.VN as vn',
                'HNOPD_MASTER.HN as hn',
                'HNPAT_NAME.FirstName as name',
                'HNPAT_NAME.LastName as lastname',
                'HNOPD_PRESCRIP_DIAG.ICDCode as icd',
            )
            ->orderBy('HNOPD_MASTER.VisitDate', 'DESC')
            ->get();

        foreach ($visits as $datavisit) {
            if ($outputData[$datavisit->hn]['show'] == false) {
                $outputData[$datavisit->hn]['show']     = true;
                $outputData[$datavisit->hn]['name']     = HelperController::FullName($datavisit->name, $datavisit->lastname);
                $outputData[$datavisit->hn]['diagDate'] = [
                    'date' => $datavisit->date,
                    'vn'   => $datavisit->vn,
                    'icd'  => $datavisit->icd,
                ];
            }

        }

        $total = 0;
        foreach ($outputData as $i => $data) {
            if ($data['show'] == false) {
                unset($outputData[$i]);
            } else {
                foreach ($data['appointment'] as $apno => $ap) {
                    $total += 1;
                    $text = null;
                    foreach ($ap['status'] as $st) {
                        if ($st == 1 && $text !== 'Cancel' && $text !== 'Attend') {
                            $text = 'Add';
                        }
                        if ($st == 4) {
                            $text = 'Cancel';
                            if ($dateQuery['cancel'] == 'false') {
                                unset($outputData[$i]['appointment'][$apno]);
                            }
                        }
                        if ($st == 12) {
                            $text = 'Attend';
                        }
                    }
                    if ($dateQuery['cancel'] == 'false' && $text == 'Cancel') {
                        $text = null;
                    }
                    if ($text !== null) {
                        $outputData[$i]['appointment'][$apno]['status'] = $text;
                    }

                }

                if (count($outputData[$i]['appointment']) == 0) {
                    unset($outputData[$i]);
                    $total -= 1;
                }
            }
        }

        return view('a7z94')->with(compact('outputData', 'total', 'dateQuery'));
    }
    public function A7Z94XRAY(Request $request)
    {
        $dateQuery = [
            'start' => date('Y-m-d'),
            'end'   => date('Y-m-d'),
        ];
        $req = $request->query;
        foreach ($req as $in => $value) {
            if ($in == 'start') {
                $dateQuery['start'] = $value;
            }
            if ($in == 'end') {
                $dateQuery['end'] = $value;
            }
        }

        $xrayCode = ['XU0004', 'XU0006', 'XU0054'];
        $visits   = DB::connection('SSB')
            ->table("HNOPD_MASTER")
            ->join("HNOPD_PRESCRIP_TREATMENT", function ($join) {
                $join->on('HNOPD_MASTER.VisitDate', '=', 'HNOPD_PRESCRIP_TREATMENT.VisitDate');
                $join->on('HNOPD_MASTER.VN', '=', 'HNOPD_PRESCRIP_TREATMENT.VN');
            })
            ->whereDate('HNOPD_MASTER.VisitDate', '>=', $dateQuery['start'])
            ->whereDate('HNOPD_MASTER.VisitDate', '<=', $dateQuery['end'])
            ->whereIn('HNOPD_PRESCRIP_TREATMENT.XrayCode', $xrayCode)
            ->select(
                'HNOPD_MASTER.VisitDate',
                'HNOPD_MASTER.VN',
                'HNOPD_MASTER.HN',
                'HNOPD_PRESCRIP_TREATMENT.XrayCode',
            )
            ->get();

        $HNArray    = [];
        $outputData = [];

        foreach ($visits as $key => $value) {
            if (! in_array($value->HN, $HNArray)) {
                $HNArray[] = $value->HN;
            }
            if (! array_key_exists($value->HN, $outputData)) {
                $outputData[$value->HN]['show'] = false;
                $outputData[$value->HN]['date'] = $value->VisitDate;
                $outputData[$value->HN]['xray'] = $value->XrayCode;
            }
        }

        $diags = DB::connection('SSB')
            ->table("HNOPD_MASTER")
            ->leftjoin("HNPAT_NAME", 'HNOPD_MASTER.HN', '=', 'HNPAT_NAME.HN')
            ->join("HNOPD_PRESCRIP_DIAG", function ($join) {
                $join->on('HNOPD_MASTER.VisitDate', '=', 'HNOPD_PRESCRIP_DIAG.VisitDate');
                $join->on('HNOPD_MASTER.VN', '=', 'HNOPD_PRESCRIP_DIAG.VN');
            })
            ->where('HNPAT_NAME.SuffixSmall', 0)
            ->whereIn('HNOPD_MASTER.HN', $HNArray)
            ->where('HNOPD_PRESCRIP_DIAG.ICDCode', 'z94.0')
            ->select(
                'HNOPD_MASTER.Visitdate',
                'HNOPD_MASTER.VN',
                'HNOPD_MASTER.HN',
                'HNPAT_NAME.FirstName',
                'HNPAT_NAME.lastName',
                'HNOPD_PRESCRIP_DIAG.ICDCode as icd',
            )
            ->orderBy('HNOPD_MASTER.VisitDate', 'DESC')
            ->get();

        foreach ($diags as $item) {
            if ($outputData[$item->HN]['show'] == false) {
                $outputData[$item->HN]['show'] = true;
                $outputData[$item->HN]['name'] = HelperController::FullName($item->FirstName, $item->lastName);
                $outputData[$item->HN]['icd']  = [
                    'date' => $item->Visitdate,
                    'vn'   => $item->VN,
                    'icd'  => $item->icd,
                ];
            }
        }

        $total = 0;
        foreach ($outputData as $i => $data) {
            if ($data['show'] == false) {
                unset($outputData[$i]);
            } else {
                $total += 1;
            }
        }

        return view('a7z94Xray')->with(compact('outputData', 'total', 'dateQuery'));

    }
    public function visitLineOA()
    {
        $start = '2024-10-10';
        $end   = '2025-01-29';

        $dateStart = date_create($start);
        $dateEnd   = date_create($end);
        $diff      = date_diff($dateStart, $dateEnd);

        $outputData = [];
        for ($i = 1; $i <= $diff->days + 1; $i++) {
            $date = date_format($dateStart, 'Y-m-d');

            $output = DB::connection("SSB")
                ->table("HNOPD_MASTER")
                ->whereDate('VisitDate', $date)
                ->groupBy('HN')
                ->select('HN')
                ->get();

            $arHN = [];
            foreach ($output as $hn) {
                $arHN[] = $hn->HN;
            }
            $dataNull = DB::connection('SSB')
                ->table('HNPAT_INFO')
                ->whereIn('HN', $arHN)
                ->whereNull('LineID')
                ->count();

            $dataNotNull = DB::connection('SSB')
                ->table('HNPAT_INFO')
                ->whereIn('HN', $arHN)
                ->whereNotNull('LineID')
                ->count();
            $outputData[$date] = [
                'onLine'  => $dataNull,
                'onLine%' => round($dataNull / count($output) * 100, 2),
                'inLine'  => $dataNotNull,
                'inLine%' => round($dataNotNull / count($output) * 100, 2),
                'total'   => count($output),
            ];

            $dateStart = date_add($dateStart, date_interval_create_from_date_string("1 days"));
        }

        return view('visitLineOA')->with(compact('outputData'));
    }
    public function PDPA3()
    {
        $dataLine = DB::connection('PDPA')
            ->table('Concent_TH')
            ->where('Userlogon', 'API-LINEOA')
            ->where('PDPA3', 'ไม่ยินยอมประกัน')
            ->get();

        $hn   = [];
        $data = [];
        foreach ($dataLine as $row) {
            if (! in_array($row->HN, $hn)) {
                $hn[]   = $row->HN;
                $data[] = [
                    'HN'             => $row->HN,
                    'Name'           => $row->Name_TH . ' ' . $row->Surname_TH,
                    'PDPA3'          => $row->PDPA3,
                    'PDPA4'          => $row->PDPA4,
                    'Witness'        => $row->Witness,
                    'Userlogon'      => $row->Userlogon,
                    'CreateDateTime' => $row->CreateDateTime,
                ];
            }
        }

        foreach ($hn as $item) {
            $dataRow = DB::connection('PDPA')
                ->table('Concent_TH')
                ->where('HN', $item)
                ->orderBy('CreateDateTime', 'asc')
                ->where('Userlogon', '!=', 'API-LINEOA')
                ->first();

            if ($dataRow !== null) {
                $data[] = [
                    'HN'             => $dataRow->HN,
                    'Name'           => $dataRow->Name_TH . ' ' . $dataRow->Surname_TH,
                    'PDPA3'          => $dataRow->PDPA3,
                    'PDPA4'          => $dataRow->PDPA4,
                    'Witness'        => $dataRow->Witness,
                    'Userlogon'      => $dataRow->Userlogon,
                    'CreateDateTime' => $dataRow->CreateDateTime,
                ];
            } else {
                $data[] = [
                    'HN'             => $item,
                    'Name'           => 'ไม่พบข้อมูลนอกเหนือจาก API-LINEOA',
                    'PDPA3'          => null,
                    'PDPA4'          => null,
                    'Witness'        => null,
                    'Userlogon'      => null,
                    'CreateDateTime' => null,
                ];
            }
        }
        usort($data, function ($item1, $item2) {return $item1['HN'] <=> $item2['HN'];});

        return view('linePDPA')->with(compact('data'));
    }

    public function dental()
    {
        $dateFilter = [
            'start'   => '2024-01-01',
            'end'     => '2024-04-30',
            'AppDate' => '2024-07-01',
        ];
        $doctorName = DB::connection('SSB')->table("HNDOCTOR_MASTER")->get();
        $data       = DB::connection("SSB")
            ->table("HNOPD_MASTER")
            ->leftjoin('HNPAT_NAME', 'HNOPD_MASTER.HN', 'HNPAT_NAME.HN')
            ->join('HNOPD_PRESCRIP', function ($join) {
                $join->on('HNOPD_MASTER.VisitDate', '=', 'HNOPD_PRESCRIP.VisitDate')
                    ->on('HNOPD_MASTER.VN', '=', 'HNOPD_PRESCRIP.VN');
            })
            ->join('HNPAT_INFO', 'HNOPD_MASTER.HN', 'HNPAT_INFO.HN')
            ->whereDate('HNOPD_MASTER.VisitDate', '>=', $dateFilter['start'])
            ->whereDate('HNOPD_MASTER.VisitDate', '<=', $dateFilter['end'])
            ->where('HNOPD_PRESCRIP.Clinic', '1100')
            ->where('HNPAT_NAME.SuffixSmall', 0)
            ->where('HNPAT_INFO.BirthDateTime', '>=', '2009-01-01')
            ->select(
                'HNOPD_MASTER.HN',
                'HNOPD_PRESCRIP.Doctor',
                'HNOPD_MASTER.VisitDate',
                'HNPAT_NAME.FirstName',
                'HNPAT_NAME.LastName',
                'HNPAT_INFO.BirthDateTime',
                'HNPAT_INFO.Gender',
            )
            ->orderBy('HNOPD_MASTER.VisitDate', 'asc')
        // ->orderBy('HNPAT_INFO.HN','asc')
        // ->orderBy('HNPAT_INFO.BirthDateTime','asc')
            ->get();

        $hnArr  = [];
        $output = [];
        foreach ($data as $item) {
            if (! in_array($item->HN, $hnArr)) {
                $hnArr[]           = $item->HN;
                $output[$item->HN] = [
                    'hn'     => $item->HN,
                    'name'   => HelperController::FullName($item->FirstName, $item->LastName),
                    'gender' => ($item->Gender == '1') ? 'หญิง' : 'ชาย',
                    'age'    => HelperController::setAge($item->BirthDateTime),
                    'visit'  => HelperController::SetDate($item->VisitDate, 'd M Y'),
                    'doctor' => HelperController::DoctorName($doctorName, $item->Doctor),
                    'app'    => [],
                ];
            }
        }
        $time  = ceil(count($hnArr) / 1000);
        $hnArr = array_chunk($hnArr, 1000);
        for ($i = 0; $i < $time; $i++) {
            $app = DB::connection('SSB')
                ->table('HNAPPMNT_HEADER')
                ->whereIn('HN', $hnArr[$i])
                ->whereDate('AppointDateTime', '>=', $dateFilter['AppDate'])
                ->where('AppmntProcedureCode1', 'A746')
            // ->whereNull('CxlReasonCode')
                ->select(
                    'HN',
                    'AppointmentNo',
                    'AppointDateTime',
                )
                ->orderBy('AppointDateTime', 'asc')
                ->get();
            foreach ($app as $a) {
                $findAttend = DB::connection('SSB')
                    ->table('HNAPPMNT_LOG')
                    ->where('AppointmentNo', $a->AppointmentNo)
                    ->get();

                $output[$a->HN]['app'][$a->AppointmentNo] = [
                    'No'     => $a->AppointmentNo,
                    'date'   => HelperController::SetDate($a->AppointDateTime, 'd M Y'),
                    'status' => ($a->AppointDateTime > date('Y-m-d')) ? 'Future' : 'Missing',
                ];

                foreach ($findAttend as $r) {
                    if ($r->HNAppointmentLogType == '12') {
                        $output[$a->HN]['app'][$a->AppointmentNo]['status'] = 'Attend';
                    }

                }
            }
        }

        return view('dental')->with(compact('output', 'dateFilter'));
    }
    public function loyalty()
    {
        $contents = File::get(base_path('public/file/loyolty.json'));
        $contents = json_decode($contents, true);
        $data     = [];
        foreach ($contents as $item) {
            $card = DB::connection('SSB')
                ->table('HNPAT_REF')
                ->where('HN', $item['hn'])
                ->first();
            $data[] = [
                'hn'            => $item['hn'],
                'name'          => $item['name'],
                'line_id'       => $item['line_id'],
                'register_date' => $item['register_date'],
                'idcard'        => ($card !== null) ? $card->RefNo : 'Not Found',
            ];
        }
        Storage::disk('public')->put('loyalty.json', json_encode($data, JSON_UNESCAPED_UNICODE));
    }
    public function lineOALog(Request $request)
    {
        $dateQuery = [
            'start' => date('Y-m-d'),
            'end'   => date('Y-m-d'),
        ];

        $req = $request->query;
        foreach ($req as $in => $value) {
            if ($in == 'start') {
                $dateQuery['start'] = $value;
            }
            if ($in == 'end') {
                $dateQuery['end'] = $value;
            }
        }

        mb_internal_encoding('UTF-8');
        $date    = date('Y-m-d H:i:s');
        $findLog = DB::connection('SSB')
            ->table("HNPAT_LOG")
            ->whereDate('MakeDateTime', '>=', $dateQuery['start'])
            ->whereDate('MakeDateTime', '<=', $dateQuery['end'])
            ->where('EntryByUserCode', 'API-LINEOA')
        // ->where('EntryByUserCode', 'Tablet')
            ->orderBy('MakeDateTime', 'ASC')
            ->get();
        $hn     = [];
        $output = [];
        foreach ($findLog as $item) {
            $hninfo = DB::connection('SSB')
                ->table('HNPAT_INFO')
                ->join('HNPAT_REF', 'HNPAT_INFO.HN', 'HNPAT_REF.HN')
                ->join('HNPAT_NAME', 'HNPAT_INFO.HN', '=', 'HNPAT_NAME.HN')
                ->join('HNPAT_Address', 'HNPAT_INFO.HN', '=', 'HNPAT_Address.HN')
                ->leftjoin('HNPAT_EMAIL', 'HNPAT_INFO.HN', '=', 'HNPAT_EMAIL.HN')
                ->where('HNPAT_NAME.SuffixSmall', 0)
                ->where('HNPAT_Address.SuffixTiny', 1)
                ->where('HNPAT_INFO.HN', $item->HN)
                ->select(
                    'HNPAT_INFO.HN',
                    'HNPAT_INFO.LineID',
                    'HNPAT_NAME.Firstname',
                    'HNPAT_NAME.Lastname',
                    'HNPAT_REF.RefNO',
                    'HNPAT_Address.MobilePhone',
                    'HNPAT_EMAIL.EMailAddress'
                )
                ->orderBy('HNPAT_EMAIL.EMailAddress', 'asc')
                ->first();
            if (! in_array($item->HN, $hn)) {
                $hn[] = $item->HN;
                try {
                    //code...
                    $output[] = [
                        'Date'   => date('d M Y H:i:s', strtotime($item->MakeDateTime)),
                        'HN'     => $item->HN,
                        'Name'   => mb_substr($hninfo->Firstname, 1) . ' ' . mb_substr($hninfo->Lastname, 1),
                        'Ref'    => $hninfo->RefNO,
                        'Phone'  => $hninfo->MobilePhone,
                        'LineID' => $hninfo->LineID,
                        'email'  => $hninfo->EMailAddress,
                    ];
                } catch (\Throwable $th) {
                    $hninfo = DB::connection('SSB')
                        ->table('HNPAT_INFO')
                        ->join('HNPAT_REF', 'HNPAT_INFO.HN', 'HNPAT_REF.HN')
                        ->join('HNPAT_NAME', 'HNPAT_INFO.HN', '=', 'HNPAT_NAME.HN')
                        ->leftjoin('HNPAT_Address', 'HNPAT_INFO.HN', '=', 'HNPAT_Address.HN')
                        ->leftjoin('HNPAT_EMAIL', 'HNPAT_INFO.HN', '=', 'HNPAT_EMAIL.HN')
                        ->where('HNPAT_NAME.SuffixSmall', 1)
                        ->where('HNPAT_Address.SuffixTiny', 1)
                        ->where('HNPAT_INFO.HN', $item->HN)
                        ->select(
                            'HNPAT_INFO.HN',
                            'HNPAT_INFO.LineID',
                            'HNPAT_NAME.Firstname',
                            'HNPAT_NAME.Lastname',
                            'HNPAT_REF.RefNO',
                            'HNPAT_Address.MobilePhone',
                        )
                        ->first();
                    if ($hninfo == null) {
                        $output[] = [
                            'Date'   => date('d M Y H:i:s', strtotime($item->MakeDateTime)),
                            'HN'     => $item->HN,
                            'Name'   => 'not found',
                            'Ref'    => null,
                            'Phone'  => null,
                            'LineID' => null,
                            'email'  => null,
                        ];
                        continue;
                    }
                    $output[] = [
                        'Date'   => date('d M Y H:i:s', strtotime($item->MakeDateTime)),
                        'HN'     => $item->HN,
                        'Name'   => mb_substr($hninfo->Firstname, 1) . ' ' . mb_substr($hninfo->Lastname, 1),
                        'Ref'    => $hninfo->RefNO,
                        'Phone'  => $hninfo->MobilePhone,
                        'LineID' => $hninfo->LineID,
                        'email'  => $hninfo->EMailAddress,
                    ];
                }

            }
        }

        return view('lineOALog')->with(compact('output', 'date', 'dateQuery'));
    }
    public function LinePrescript()
    {
        $date     = date('Y-m-d');
        $dateForm = '2024-10-10';
        $dateTO   = '2024-10-11';
        $data     = DB::connection('SSB')
            ->table("HNPAT_INFO")
            ->leftJoin('HNOPD_MASTER', 'HNPAT_INFO.HN', 'HNOPD_MASTER.HN')
            ->whereNotNull('LineID')
            ->whereDate('HNOPD_MASTER.VisitDate', '>=', $dateForm)
            ->whereDate('HNOPD_MASTER.VisitDate', '<=', $dateTO)
            ->where('HNOPD_MASTER.DefaultRightCode', 'NOT LIKE', '04%')
            ->where('HNOPD_MASTER.DefaultRightCode', 'NOT LIKE', '05%')
            ->select(
                'HNPAT_INFO.HN',
                'HNPAT_INFO.LineID',
                'HNOPD_MASTER.VisitDate',
                'HNOPD_MASTER.DefaultRightCode',
            )
            ->orderBy('HNPAT_INFO.HN', 'ASC')
            ->get();
        $hn     = [];
        $output = [];
        foreach ($data as $item) {
            $findLog = DB::connection('SSB')
                ->table("HNPAT_LOG")
                ->whereDate('MakeDateTime', '>=', $dateForm)
                ->whereDate('MakeDateTime', '<=', $dateTO)
                ->where('EntryByUserCode', 'API-LINEOA')
                ->first();

            if (! in_array($item->HN, $hn) && $findLog !== null) {
                $hn[]     = $item->HN;
                $output[] = [
                    'Date'  => date('d M Y', strtotime($item->VisitDate)),
                    'HN'    => $item->HN,
                    'Right' => $item->DefaultRightCode,
                    'Line'  => $item->LineID,
                    'Err'   => (strlen($item->LineID) < 21) ? 'Invalid' : 'Valid',
                ];
            }
        }

        return view('lineOAtoday')->with(compact('output', 'dateForm', 'dateTO'));
    }
    public function ARCode()
    {
        $arrayARCode = [
            'ARD11',
            'ARD12',
            'ARD15',
            'ARD16',
            'ARD17',
            'ARD18',
        ];
        $doctorNameArray = DB::connection('SSB')->table("HNDOCTOR_MASTER")->get();
        $datas           = DB::connection("SSB")
            ->table("HNPAT_RIGHT")
            ->leftjoin('HNPAT_NAME', 'HNPAT_RIGHT.HN', '=', 'HNPAT_NAME.HN')
            ->whereIN('HNPAT_RIGHT.ARCode', $arrayARCode)
            ->where('HNPAT_NAME.SuffixSmall', 0)
            ->orderBy('HNPAT_RIGHT.ARCode', 'ASC')
            ->orderBy('HNPAT_RIGHT.HN', 'ASC')
            ->select(
                'HNPAT_RIGHT.HN',
                'HNPAT_RIGHT.ARCode',
                'HNPAT_RIGHT.ReferToDoctor',
                'HNPAT_NAME.FirstName as name',
                'HNPAT_NAME.LastName as lastname',
            )
            ->get();

        foreach ($datas as $data) {
            $data->fullname   = HelperController::FullName($data->name, $data->lastname);
            $data->DoctorName = HelperController::DoctorName($doctorNameArray, $data->ReferToDoctor);
        }
        return view('ARCode')->with(compact('datas'));
    }
    // Depression

    public function excelImport()
    {
        return view('excel-import');
    }

    public function processExcelImport(Request $request)
    {
        $request->validate([
            'excel_file' => 'required|mimes:xlsx,xls,csv|max:10240', // 10MB max
        ]);

        try {
            $file = $request->file('excel_file');
            $data = \Maatwebsite\Excel\Facades\Excel::toArray([], $file);

            // Get the first sheet
            $sheet = $data[0];

            // Start from row 5 (index 4) as requested
            $processedData = [];
            for ($i = 4; $i < count($sheet); $i++) {
                if (! empty(array_filter($sheet[$i]))) { // Skip empty rows
                    $processedData[] = [
                        "no"     => $sheet[$i][2],
                        "code"   => $sheet[$i][3],
                        "number" => $sheet[$i][4],
                        "name"   => $sheet[$i][5],
                    ];
                }
            }

            return view('excel-import', [
                'data'    => $processedData,
                'success' => 'Excel file processed successfully! Found ' . count($processedData) . ' rows starting from row 5.',
            ]);

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error processing Excel file: ' . $e->getMessage()]);
        }
    }

    public function getRandomRows(Request $request)
    {
        try {
            $excelData = json_decode($request->input('excel_data'), true);

            if (empty($excelData)) {
                return back()->withErrors(['error' => 'No data available for random selection.']);
            }

            // Get 5 random rows (or all rows if less than 5)
            $randomCount = min(5, count($excelData));
            $randomKeys  = array_rand($excelData, $randomCount);

            // Handle case where only 1 row is selected (array_rand returns int, not array)
            if (! is_array($randomKeys)) {
                $randomKeys = [$randomKeys];
            }

            $randomData = [];
            foreach ($randomKeys as $key) {
                $randomData[] = $excelData[$key];
            }

            return view('excel-import', [
                'data'       => $excelData,
                'randomData' => $randomData,
                'success'    => 'Selected ' . count($randomData) . ' random rows!',
            ]);

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error selecting random rows: ' . $e->getMessage()]);
        }
    }
}
