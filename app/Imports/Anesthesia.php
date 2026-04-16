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

    public function anesthesiaArray($anesthesiaTypeID, $hours, $th_df, $th_total, $inter_df, $inter_total, $arab_df, $arab_total)
    {
        $array = [];
        $auth = auth()->user()->name_EN;

        $array[] = [
            'Nationality' => 'Thai',
            'AnesthesiaTypeID' => $anesthesiaTypeID,
            'Hours' => $hours,
            'TotalMin' => $th_df[0],
            'TotalMax' => $th_df[1],
            '80percentile' => $th_df[0] + $th_df[1] / 2,
            'Medicine' => '20',
            'MedicineCost' => $th_df[0] + $th_df[1] / 2 * 0.2,
            'MedicalSupplies1' => '80',
            'MedicalSupplies1Cost' => $th_df[0] + $th_df[1] / 2 * 0.8,
            'TotalMinAnesthesia' => $th_total[0],
            'TotalMaxAnesthesia' => $th_total[1],
            'AnesthesiaCost' => $th_total[0] + $th_total[1] / 2,
            'CreateDate' => date('Y-m-d H:i:s'),
            'CreateBy' => $auth,
            'UpdateDate' => date('Y-m-d H:i:s'),
            'UpdateBy' => $auth,
            'HoursForAnesthesia' => $hours,
        ];

        $array[] = [
            'Nationality' => 'Inter',
            'AnesthesiaTypeID' => $anesthesiaTypeID,
            'Hours' => $hours,
            'TotalMin' => $inter_df[0],
            'TotalMax' => $inter_df[1],
            '80percentile' => $inter_df[0] + $inter_df[1] / 2,
            'Medicine' => '20',
            'MedicineCost' => $inter_df[0] + $inter_df[1] / 2 * 0.2,
            'MedicalSupplies1' => '80',
            'MedicalSupplies1Cost' => $inter_df[0] + $inter_df[1] / 2 * 0.8,
            'TotalMinAnesthesia' => $inter_total[0],
            'TotalMaxAnesthesia' => $inter_total[1],
            'AnesthesiaCost' => $inter_total[0] + $inter_total[1] / 2,
            'CreateDate' => date('Y-m-d H:i:s'),
            'CreateBy' => $auth,
            'UpdateDate' => date('Y-m-d H:i:s'),
            'UpdateBy' => $auth,
            'HoursForAnesthesia' => $hours,
        ];

        $array[] = [
            'Nationality' => 'Arab',
            'AnesthesiaTypeID' => $anesthesiaTypeID,
            'Hours' => $hours,
            'TotalMin' => $arab_df[0],
            'TotalMax' => $arab_df[1],
            '80percentile' => $arab_df[0] + $arab_df[1] / 2,
            'Medicine' => '20',
            'MedicineCost' => $arab_df[0] + $arab_df[1] / 2 * 0.2,
            'MedicalSupplies1' => '80',
            'MedicalSupplies1Cost' => $arab_df[0] + $arab_df[1] / 2 * 0.8,
            'TotalMinAnesthesia' => $arab_total[0],
            'TotalMaxAnesthesia' => $arab_total[1],
            'AnesthesiaCost' => $arab_total[0] + $arab_total[1] / 2,
            'CreateDate' => date('Y-m-d H:i:s'),
            'CreateBy' => $auth,
            'UpdateDate' => date('Y-m-d H:i:s'),
            'UpdateBy' => $auth,
            'HoursForAnesthesia' => $hours,
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
                $th_df = explode('-', str_replace(' ', '', str_replace(',', '', $row[2])));
                $th_total = explode('-', str_replace(' ', '', str_replace(',', '', $row[3])));
                $inter_df = explode('-', str_replace(' ', '', str_replace(',', '', $row[4])));
                $inter_total = explode('-', str_replace(' ', '', str_replace(',', '', $row[5])));
                $arab_df = explode('-', str_replace(' ', '', str_replace(',', '', $row[6])));
                $arab_total = explode('-', str_replace(' ', '', str_replace(',', '', $row[7])));

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

                $anesthesiaArray = $this->anesthesiaArray($findID->ID, $hours, $th_df, $th_total, $inter_df, $inter_total, $arab_df, $arab_total);

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
