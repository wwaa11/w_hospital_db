<?php
namespace App\Http\Controllers;

use DateTime;
use DB;
use File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CoreController extends Controller
{
    public function index()
    {
        return view('index');
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
                    'name'   => $this->FullName($app->FirstName, $app->LastName),
                    'clinic' => [],
                    'count'  => 0,
                ];
            }
            $data[$app->HN]['count'] += 1;
            $data[$app->HN]['clinic'][] = [
                'clinic' => $this->ClinicName($clinicName, $app->clinic),
                'doctor' => $this->DoctorName($doctorName, $app->doctor),
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
            $age = $this->setAgeInterger($hn->BirthDateTime);
            if ($age <= 2) {
                if (! in_array($hn->HN, $hnArr)) {
                    $hnArr[]             = $hn->HN;
                    $dataOutput[$hn->HN] = [
                        'show'      => true,
                        'diag'      => 'not Found',
                        'vaccine'   => 'not Found',
                        'hn'        => $hn->HN,
                        'name'      => $this->FullName($hn->name, $hn->lastname),
                        'age'       => $this->setAge($hn->BirthDateTime),
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
                    $dataOutput[$out['hn']]['lastvisit'] = $this->DoctorName($doctorName, $visitData->Doctor);
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
            $value->fullname = $this->FullName($value->Firstname, $value->Lastname);
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
                $outputData[$datavisit->hn]['name']     = $this->FullName($datavisit->name, $datavisit->lastname);
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
                $outputData[$item->HN]['name'] = $this->FullName($item->FirstName, $item->lastName);
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
                    'name'   => $this->FullName($item->FirstName, $item->LastName),
                    'gender' => ($item->Gender == '1') ? 'หญิง' : 'ชาย',
                    'age'    => $this->setAge($item->BirthDateTime),
                    'visit'  => $this->SetDate($item->VisitDate, 'd M Y'),
                    'doctor' => $this->DoctorName($doctorName, $item->Doctor),
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
                    'date'   => $this->SetDate($a->AppointDateTime, 'd M Y'),
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
            $data->fullname   = $this->FullName($data->name, $data->lastname);
            $data->DoctorName = $this->DoctorName($doctorNameArray, $data->ReferToDoctor);
        }
        return view('ARCode')->with(compact('datas'));
    }
    // Depression
    public function Depress(Request $request)
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
                    'visit'  => $this->SetDate($item->VisitDate, 'd F Y'),
                    'hn'     => $item->HN,
                    'name'   => $this->FullName($item->FirstName, $item->LastName),
                    'clinic' => $this->ClinicName($clinicName, $item->Clinic),
                    'doctor' => $this->DoctorName($doctorName, $item->Doctor),
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
                        $checkFollow[$item->HN]['name']                             = $this->FullName($item->FirstName, $item->LastName);
                        $checkFollow[$item->HN]['app'][$app->AppointmentNo]['date'] = $this->SetDate($app->AppointDateTime, 'd F Y');
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
                'visit'  => $this->SetDate($item->VisitDate, 'd F Y'),
                'hn'     => $item->HN,
                'name'   => $this->FullName($item->FirstName, $item->LastName),
                'clinic' => $this->ClinicName($clinicName, $item->Clinic),
                'doctor' => $this->DoctorName($doctorName, $item->Doctor),
            ];
        }

        $date = [
            'date_1_form' => $this->setFullDate($startDate),
            'date_1_to'   => $this->setFullDate($endDate),
            'date_2_form' => $this->setFullDate($RecoveryStartDate),
            'date_2_to'   => $this->setFullDate($RecoveryEndDate),
        ];

        return view('depression')->with(compact('data', 'date', 'checkFollow'));
    }

    // Sub Function
    public function FullName($first, $last)
    {
        mb_internal_encoding('UTF-8');
        $setname = mb_substr($first, 1);
        $setlast = mb_substr($last, 1);
        if (str_contains($setname, '\\')) {
            $setname = explode("\\", $setname);
            $setname = $setname[0];
        }
        $name = $setname . " " . $setlast;

        return $name;
    }
    public function setAgeInterger($birthDate)
    {
        $now      = new DateTime();
        $date     = new DateTime($birthDate);
        $interval = $now->diff($date);
        $year     = round($interval->y . '.' . $interval->m, 0);

        return $year;
    }
    public function setAge($dateInput)
    {
        $date     = new DateTime($dateInput);
        $now      = new DateTime();
        $interval = $now->diff($date);
        $output   = $interval->y . ' Y ' . $interval->m . ' M ' . $interval->d . ' D';

        return $output;
    }
    public function DoctorName($data, $code)
    {
        $doctor = collect($data)->where('Doctor', $code)->first();
        if ($doctor !== null) {
            mb_internal_encoding('UTF-8');
            $name = mb_substr($doctor->LocalName, 1);
            if (str_contains($name, '\\')) {
                $temp = explode("\\", $name);
                $name = $temp[1] . $temp[0];
            }
        } else {
            $name = null;
        }

        return $name;
    }
    public function ClinicName($data, $code)
    {
        $clinic = collect($data)->where('Code', $code)->first();
        if ($clinic !== null) {
            mb_internal_encoding('UTF-8');
            $name = mb_substr($clinic->LocalName, 1);
        }
        return $name;
    }
    public function SetDate($date, $format)
    {
        // https://www.php.net/manual/en/datetime.format.php
        $date = strtotime($date);

        return date($format, $date);
    }
    public function setFullDate($dateInput)
    {
        $nation = 'THA';
        $date   = explode('-', $dateInput);
        $month  = $date[1];
        if ($nation == 'THA') {
            $year   = $date[0] + 543;
            $months = [
                "01" => "มกราคม",
                "02" => "กุมภาพันธ์",
                "03" => "มีนาคม",
                "04" => "เมษายน",
                "05" => "พฤษภาคม",
                "06" => "มิถุนายน",
                "07" => "กรกฎาคม",
                "08" => "สิงหาคม",
                "09" => "กันยายน",
                "10" => "ตุลาคม",
                "11" => "พฤศจิกายน",
                "12" => "ธันวาคม",
            ];
            $fullmonth = $months[$month] ?? '';
        } else {
            $year   = $date[0];
            $months = [
                "01" => "January",
                "02" => "February",
                "03" => "March",
                "04" => "April",
                "05" => "May",
                "06" => "June",
                "07" => "July",
                "08" => "August",
                "09" => "September",
                "10" => "October",
                "11" => "November",
                "12" => "December",
            ];
            $fullmonth = $months[$month] ?? '';
        }
        $date = substr($date[2], 0, 2) . " " . $fullmonth . " " . $year;

        return $date;
    }

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
