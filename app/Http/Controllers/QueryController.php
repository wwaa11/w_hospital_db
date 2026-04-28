<?php

namespace App\Http\Controllers;

use Cache;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use App\Http\Controllers\HelperController;

class QueryController extends Controller
{
    public $HelperController;

    public function __construct()
    {
        $this->HelperController = new HelperController();
    }

    public function Depression(Request $request)
    {
        $startDate = $request->input('startdate', date('Y-01-01'));
        $endDate = $request->input('enddate', date('Y-03-31'));
        $RecoveryStartDate = $request->input('recoverystartdate', date('Y-04-30'));
        $RecoveryEndDate = $request->input('recoveryenddate', date('Y-06-30'));

        $data = [
            1 => [],
            2 => [],
        ];
        $checkFollow = [];
        $doctorName = DB::connection('SSB')->table('HNDOCTOR_MASTER')->get();
        $clinicName = DB::connection('SSB')->table('DNSYSCONFIG')->where('CtrlCode', '42203')->get();

        $clinic = ['1500', '1502'];
        $icd = ['F32.0', 'F32.1', 'F32.2', 'F32.3', 'F32.4', 'F32.6', 'F32.7', 'F32.8', 'F32.9'];
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
                $hnNew[] = $item->HN;
                $data['1'][] = [
                    'visit' => HelperController::SetDate($item->VisitDate, 'd F Y'),
                    'hn' => $item->HN,
                    'name' => HelperController::FullName($item->FirstName, $item->LastName),
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
                        $checkFollow[$item->HN]['name'] = HelperController::FullName($item->FirstName, $item->LastName);
                        $checkFollow[$item->HN]['app'][$app->AppointmentNo]['date'] = HelperController::SetDate($app->AppointDateTime, 'd F Y');
                        if ($app->HNAppointmentLogType == 12) {
                            $checkFollow[$item->HN]['app'][$app->AppointmentNo]['status'] = 'Attended';
                        } elseif ($app->HNAppointmentLogType == 4) {
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
                'visit' => HelperController::SetDate($item->VisitDate, 'd F Y'),
                'hn' => $item->HN,
                'name' => HelperController::FullName($item->FirstName, $item->LastName),
                'clinic' => HelperController::ClinicName($clinicName, $item->Clinic),
                'doctor' => HelperController::DoctorName($doctorName, $item->Doctor),
            ];
        }

        $date = [
            'date_1_form' => HelperController::setFullDate($startDate),
            'date_1_to' => HelperController::setFullDate($endDate),
            'date_2_form' => HelperController::setFullDate($RecoveryStartDate),
            'date_2_to' => HelperController::setFullDate($RecoveryEndDate),
        ];

        return view('Query.depression')->with(compact('data', 'date', 'checkFollow'));
    }

    public function generateMonthlyDateBatches(string $startDate, string $endDate): array
    {
        // Convert strings to Carbon objects for easy manipulation
        $start = Carbon::parse($startDate)->startOfMonth();
        $end = Carbon::parse($endDate);
        $batches = [];
        // Loop while the current month is before or the same as the target end month
        while ($start->lessThanOrEqualTo($end)) {
            $currentMonth = $start->clone();
            // Start date is the 1st of the current month
            $startOfMonth = $currentMonth->format('Y-m-d');
            // End date is the last day of the current month
            $endOfMonth = $currentMonth->endOfMonth()->format('Y-m-d');
            $batches[] = [
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
        $startDate = '2022-11-01';
        $endDate = '2025-12-31';

        // Define a unique cache key based on the date range
        $cacheKey = 'appointment_stats_'.$startDate.'_'.$endDate;
        // Use Cache::remember to fetch data from cache if available (for 1 day),
        // or execute the closure to fetch and store the data if not.
        $data = Cache::remember($cacheKey, now()->addDay(), function () use ($startDate, $endDate) {
            // Generate the monthly batch ranges
            $monthlyBatches = $this->generateMonthlyDateBatches($startDate, $endDate);
            $data = [];
            // --- 1. Data Fetching and Counting Loop (Per Month) ---
            foreach ($monthlyBatches as $b) {
                $start = $b[0];
                $end = $b[1];

                // Database query for the current monthly batch
                $apps = DB::connection('SSB')
                    ->table('HNAPPMNT_HEADER')
                    ->leftJoin('HNAPPMNT_LOG', 'HNAPPMNT_HEADER.AppointmentNo', 'HNAPPMNT_LOG.AppointmentNo')
                    ->whereNull('CxlReasonCode')
                    ->where('AppointDateTime', '>=', $start)
                    ->where('AppointDateTime', '<=', $end)
                    ->whereIn('HNAPPMNT_LOG.HNAppointmentLogType', ['1', '12']) // '1' (Create) and '12' (Attend)
                    ->select(
                        'HNAPPMNT_HEADER.AppointmentNo',
                        'HNAPPMNT_LOG.HNAppointmentLogType'
                    )
                    ->get();

                // Initialize the monthly metrics array
                $data[$start] = [
                    'ssb' => 0,
                    'ssb-create' => 0,
                    'ssb-attend' => 0,
                    'online' => 0,
                    'online-create' => 0,
                    'online-attend' => 0,
                    'online-type' => [],
                ];

                $countAPP = []; // Tracks unique AppointmentNo for overall ssb/online counts
                $countAPPtype = []; // Tracks unique AppointmentNo for online-type counts

                // Process the results for the current month
                foreach ($apps as $app) {
                    // Determine the main type: 'ssb' if starts with 'AP', otherwise 'online'
                    $type = (substr($app->AppointmentNo, 0, 2) == 'AP') ? 'ssb' : 'online';

                    // Count Total Appointments (unique AppointmentNo only)
                    if (! in_array($app->AppointmentNo, $countAPP)) {
                        $countAPP[] = $app->AppointmentNo;
                        $data[$start][$type]++;
                    }

                    // Count Create or Attend logs
                    if ($app->HNAppointmentLogType == '1') {
                        $data[$start][$type.'-create']++;
                    } else { // LogType '12'
                        $data[$start][$type.'-attend']++;
                    }

                    // Sub-type counting (for 'online' appointments only)
                    if ($type == 'online') {
                        $subtype = substr($app->AppointmentNo, 0, 3);

                        // Ensure the subtype is initialized
                        if (! isset($data[$start]['online-type'][$subtype])) {
                            $data[$start]['online-type'][$subtype] = ['count' => 0];
                        }

                        // Count unique appointments for this specific subtype
                        if (! in_array($app->AppointmentNo, $countAPPtype)) {
                            $countAPPtype[] = $app->AppointmentNo;
                            $data[$start]['online-type'][$subtype]['count']++;
                        }
                    }
                }
            } // End of main monthly loop

            // --- 2. Final Percentage Calculation (Runs once on the complete dataset) ---

            // Helper function for clean division and formatting
            $formatPercent = fn ($numerator, $denominator) => $denominator > 0
                ? number_format(($numerator / $denominator) * 100, 2).'%'
                : 'N/A (Div by zero)';

            foreach ($data as $date => &$metrics) {
                $total = $metrics['ssb'] + $metrics['online'];
                $metrics['total'] = $total;

                // Overall SSB/Online Percentage
                $metrics['ssb-percent'] = $formatPercent($metrics['ssb'], $total);
                $metrics['online-percent'] = $formatPercent($metrics['online'], $total);

                // Missed Appointments
                $metrics['ssb-miss'] = $metrics['ssb'] - $metrics['ssb-attend'];
                $metrics['ssb-miss-percent'] = $formatPercent($metrics['ssb-miss'], $total);
                $metrics['online-miss'] = $metrics['online'] - $metrics['online-attend'];
                $metrics['online-miss-percent'] = $formatPercent($metrics['online-miss'], $total);

                // Attendance Percentages
                $metrics['ssb-attend-percent'] = $formatPercent($metrics['ssb-attend'], $total);
                $metrics['online-attend-percent'] = $formatPercent($metrics['online-attend'], $total);

                // Sub-type percentages (relative to total 'online' appointments for that month)
                foreach ($metrics['online-type'] as $subtype => &$submetrics) {
                    $submetrics['percent'] = $formatPercent($submetrics['count'], $metrics['online']);
                }
            }

            // Return the final data structure to be cached
            return $data;
        });

        return view('Query.appointment-percentage', compact('data'));
    }

    public function newPatientOnline()
    {
        $startDate = '2022-11-01';
        $endDate = '2025-12-31';

        $cacheKey = 'newpatientonline_stats_'.$startDate.'_'.$endDate;

        $data = Cache::remember($cacheKey, now()->addDay(), function () use ($startDate, $endDate) {
            $monthlyBatches = $this->generateMonthlyDateBatches($startDate, $endDate);
            $data = [];
            foreach ($monthlyBatches as $b) {
                $start = $b[0];
                $end = $b[1];

                $patients = DB::connection('SSB')
                    ->table('HNPAT_FILEMOVE')
                    ->where('MovementDateTime', '>=', $start)
                    ->where('MovementDateTime', '<=', $end)
                    ->where('HNFileMovementType', 1)
                    ->select(
                        'MovementDateTime',
                        'HNFileMovementType',
                        'MakeByUserCode',
                        DB::raw("
                        CASE
                            WHEN ISNUMERIC(SUBSTRING(MakeByUserCode, 1, 1)) = 1
                            THEN 'ssb'
                            ELSE 'online'
                        END AS SourceSystem
                    ")
                    )
                    ->orderBy('MovementDateTime', 'asc')
                    ->get();

                foreach ($patients as $patient) {
                    if (! isset($data[$start])) {
                        $data[$start] = [
                            'ssb' => 0,
                            'online' => 0,
                            'online-type' => [],
                        ];
                    }

                    $data[$start][$patient->SourceSystem]++;

                    if ($patient->SourceSystem == 'online') {
                        $subtype = $patient->MakeByUserCode;
                        if (! isset($data[$start]['online-type'][$subtype])) {
                            $data[$start]['online-type'][$subtype] = ['count' => 0];
                        }
                        $data[$start]['online-type'][$subtype]['count']++;
                    }
                }

                $formatPercent = fn ($numerator, $denominator) => $denominator > 0
                    ? number_format(($numerator / $denominator) * 100, 2).'%'
                    : 'N/A (Div by zero)';

                foreach ($data as $date => &$metrics) {
                    $total = $metrics['ssb'] + $metrics['online'];
                    $metrics['total'] = $total;
                    $metrics['ssb-percent'] = $formatPercent($metrics['ssb'], $total);
                    $metrics['online-percent'] = $formatPercent($metrics['online'], $total);
                    foreach ($metrics['online-type'] as $subtype => &$submetrics) {
                        $submetrics['percent'] = $formatPercent($submetrics['count'], $metrics['online']);
                    }
                }
            }

            return $data;
        });

        return view('Query.newpatient-percentage', compact('data'));
    }

    public function getAppointment()
    {
        $start = '2026-03-01';
        $end = '2026-05-30';

        $appointments = DB::connection('SSB')
            ->table('HNAPPMNT_HEADER')
            ->leftJoin('HNPAT_NAME', 'HNAPPMNT_HEADER.HN', 'HNPAT_NAME.HN')
            ->whereNull('CxlReasonCode')
            ->where('HNPAT_NAME.SuffixSmall', 0)
            ->where('AppointDateTime', '>=', $start)
            ->where('AppointDateTime', '<=', $end)
            ->where('Doctor', 'V9999')
            ->select(
                'HNAPPMNT_HEADER.AppointmentNo',
                'HNAPPMNT_HEADER.AppointDateTime',
                'HNAPPMNT_HEADER.HN',
                'HNPAT_NAME.FirstName',
                'HNPAT_NAME.LastName',
            )
            ->orderBy('HNAPPMNT_HEADER.AppointDateTime', 'asc')
            ->get();

        $data = [
            'SAP' => [],
            'EVAP' => [],
            'VAP' => [],
        ];
        foreach ($appointments as $appointment) {
            $appointment->FullName = HelperController::FullName($appointment->FirstName, $appointment->LastName);
            $substring = substr($appointment->AppointmentNo, 0, 1);
            switch ($substring) {
                case 'S':
                    $data['SAP'][] = $appointment;
                    break;
                case 'E':
                    $data['EVAP'][] = $appointment;
                    break;
                case 'V':
                    $data['VAP'][] = $appointment;
                    break;
            }
        }
        
        return view('Query.appointments', compact('data'));
    }

    public function getSSBAppointment()
    {
        $start = '2026-04-30';
        $end = '2026-05-08';

        $clinics = DB::connection('SSB')->table('DNSYSCONFIG')->where('CtrlCode', '42203')->get();

        $appointments = DB::connection('SSB')
            ->table('HNAPPMNT_HEADER')
            ->whereNull('CxlReasonCode')
            ->where('AppointDateTime', '>=', $start)
            ->where('AppointDateTime', '<=', $end)
            ->select(
                'HNAPPMNT_HEADER.AppointmentNo',
                'HNAPPMNT_HEADER.AppointDateTime',
                'HNAPPMNT_HEADER.HN',
                'HNAPPMNT_HEADER.Clinic',
            )
            ->orderBy('HNAPPMNT_HEADER.AppointDateTime', 'asc')
            ->get();
        $data = [];
        $dateTotal = [];
        foreach ($appointments as $appointment) {
            $clinic = $this->HelperController::ClinicName($clinics, $appointment->Clinic);
            $appDate = date('Y-m-d', strtotime($appointment->AppointDateTime));
            if (! isset($data[$clinic])) {
                $data[$clinic] = [
                    '2026-04-30' => 0,
                    '2026-05-01' => 0,
                    '2026-05-02' => 0,
                    '2026-05-03' => 0,
                    '2026-05-04' => 0,
                    '2026-05-05' => 0,
                    '2026-05-06' => 0,
                    '2026-05-07' => 0,
                    '2026-05-08' => 0,
                ];
            }
            $data[$clinic][$appDate]++;
            if(! isset($dateTotal[$appDate])) {
                $dateTotal[$appDate] = 0;
            }
            $dateTotal[$appDate]++;
        }
        
        return view('Query.ssb-appointments', compact('data', 'dateTotal'));
    }
}
