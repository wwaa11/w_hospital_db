<?php

namespace App\Imports;

use App\Services\K2Logger;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;

class Anesthesia implements ToCollection
{
    protected $environment;

    protected $logger;

    public function __construct($environment)
    {
        $this->environment = $environment;
        $this->logger = new K2Logger;
    }

    public function anesthesiaArray($anesthesiaTypeID, $hours, $hours_cal, $th_df, $th_total, $inter_df, $inter_total, $arab_df, $arab_total)
    {
        $array = [];
        $auth = auth()->user()->name_EN;

        $thai80DF = ($th_df[1] - $th_df[0]) * 0.8 + $th_df[0];
        $thai80 = ((($th_total[1] - $th_total[0]) * 0.8) + $th_total[0]) - $thai80DF;
        $array[] = [
            'Nationality' => 'Thai',
            'AnesthesiaTypeID' => $anesthesiaTypeID,
            'Hours' => $hours,
            'TotalMin' => $th_total[0],
            'TotalMax' => $th_total[1],
            '80percentile' => $thai80,
            'Medicine' => '20',
            'MedicineCost' => $thai80 * 0.2,
            'MedicalSupplies1' => '80',
            'MedicalSupplies1Cost' => $thai80 * 0.8,
            'TotalMinAnesthesia' => $th_df[0],
            'TotalMaxAnesthesia' => $th_df[1],
            'AnesthesiaCost' => $thai80DF,
            'CreateDate' => date('Y-m-d H:i:s'),
            'CreateBy' => $auth,
            'UpdateDate' => date('Y-m-d H:i:s'),
            'UpdateBy' => $auth,
            'HoursForAnesthesia' => $hours_cal,
        ];

        $inter80DF = ($inter_df[1] - $inter_df[0]) * 0.8 + $inter_df[0];
        $inter80 = ((($inter_total[1] - $inter_total[0]) * 0.8) + $inter_total[0]) - $inter80DF;
        $array[] = [
            'Nationality' => 'Inter',
            'AnesthesiaTypeID' => $anesthesiaTypeID,
            'Hours' => $hours,
            'TotalMin' => $inter_total[0],
            'TotalMax' => $inter_total[1],
            '80percentile' => $inter80,
            'Medicine' => '20',
            'MedicineCost' => $inter80 * 0.2,
            'MedicalSupplies1' => '80',
            'MedicalSupplies1Cost' => $inter80 * 0.8,
            'TotalMinAnesthesia' => $inter_df[0],
            'TotalMaxAnesthesia' => $inter_df[1],
            'AnesthesiaCost' => $inter80DF,
            'CreateDate' => date('Y-m-d H:i:s'),
            'CreateBy' => $auth,
            'UpdateDate' => date('Y-m-d H:i:s'),
            'UpdateBy' => $auth,
            'HoursForAnesthesia' => $hours_cal,
        ];

        $arab80DF = ($arab_df[1] - $arab_df[0]) * 0.8 + $arab_df[0];
        $arab80 = ((($arab_total[1] - $arab_total[0]) * 0.8) + $arab_total[0]) - $arab80DF;
        $array[] = [
            'Nationality' => 'Arab',
            'AnesthesiaTypeID' => $anesthesiaTypeID,
            'Hours' => $hours,
            'TotalMin' => $arab_total[0],
            'TotalMax' => $arab_total[1],
            '80percentile' => $arab80,
            'Medicine' => '20',
            'MedicineCost' => $arab80 * 0.2,
            'MedicalSupplies1' => '80',
            'MedicalSupplies1Cost' => $arab80 * 0.8,
            'TotalMinAnesthesia' => $arab_df[0],
            'TotalMaxAnesthesia' => $arab_df[1],
            'AnesthesiaCost' => $arab80DF,
            'CreateDate' => date('Y-m-d H:i:s'),
            'CreateBy' => $auth,
            'UpdateDate' => date('Y-m-d H:i:s'),
            'UpdateBy' => $auth,
            'HoursForAnesthesia' => $hours_cal,
        ];

        return $array;
    }

    public function collection(Collection $rows)
    {
        mb_internal_encoding('UTF-8');
        $auth = auth()->user()->name_EN;

        foreach ($rows as $rowIndex => $row) {
            if ($rowIndex >= 1 && $row[0] !== null) {
                $name = $row[0];
                $hours = $row[1];
                $hours_cal = $row[2];
                $th_df = explode('-', str_replace(' ', '', str_replace(',', '', $row[3])));
                $th_total = explode('-', str_replace(' ', '', str_replace(',', '', $row[4])));
                $inter_df = explode('-', str_replace(' ', '', str_replace(',', '', $row[5])));
                $inter_total = explode('-', str_replace(' ', '', str_replace(',', '', $row[6])));
                $arab_df = explode('-', str_replace(' ', '', str_replace(',', '', $row[7])));
                $arab_total = explode('-', str_replace(' ', '', str_replace(',', '', $row[8])));

                $findID = DB::connection($this->environment)->table('m_AnesthesiaType')->where('AnesthesiaName', $name)->where('Status', 'Active')->first();
                if (!$findID) {
                    $findID = DB::connection($this->environment)->table('m_AnesthesiaType')->insertGetId([
                        'AnesthesiaName' => $name,
                        'CreateDate' => date('Y-m-d H:i:s'),
                        'CreateBy' => $auth,
                        'UpdateBy' => $auth,
                        'ORNursingType' => 'Major',
                        'Status' => 'Active',
                    ]);
                }else{
                    DB::connection($this->environment)->table('m_AnesthesiaType')->where('AnesthesiaName', $name)->where('Status', 'Active')->update([
                        'UpdateDate' => date('Y-m-d H:i:s'),
                        'UpdateBy' => $auth,
                    ]);
                }

                $anesthesiaArray = $this->anesthesiaArray($findID->ID, $hours, $hours_cal, $th_df, $th_total, $inter_df, $inter_total, $arab_df, $arab_total);
                foreach ($anesthesiaArray as $anesthesia) {
                    $findHours = DB::connection($this->environment)
                    ->table('m_Anesthesia')
                    ->where('AnesthesiaTypeID', $findID->ID)
                    ->where('Nationality', $anesthesia['Nationality'])
                    ->where('Hours', $anesthesia['Hours'])
                    ->first();

                    if (!$findHours) {
                        DB::connection($this->environment)->table('m_Anesthesia')->Insert($anesthesia);
                    }else{
                        DB::connection($this->environment)->table('m_Anesthesia')->where('ID', $findHours->ID)->update($anesthesia);
                    }
                }
            }
        }
    }
}
