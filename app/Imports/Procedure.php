<?php
namespace App\Imports;

use DB;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class Procedure implements ToCollection
{
    public function medSuppilesArray($id, $price, $national, $setting, $insert)
    {
        $name         = $setting['name'];
        $pricePercent = $price / 100;
        $medicine     = $setting['med_percent'];
        $suppiles     = $setting['supplies_percent'];
        $equipment    = $setting['equipment_percent'];
        $dateTime     = date('Y-m-d H:i:s');

        if ($insert) {
            $medsuppilesArray = [
                "ProcedureID"          => $id,
                "Nationality"          => $national,
                "Total"                => $price,
                "Medicine"             => $medicine,
                "MedicalSupplies1"     => $suppiles,
                "Equipment"            => $equipment,
                "CreateDate"           => $dateTime,
                "CreateBy"             => $name,
                "UpdateDate"           => $dateTime,
                "UpdateBy"             => $name,
                "MedicineCost"         => $pricePercent * $medicine,
                "MedicalSupplies1Cost" => $pricePercent * $suppiles,
                "EquipmentCost"        => $pricePercent * $equipment,
                "Status"               => "Active",
            ];
        } else {
            $medsuppilesArray = [
                "",
                $id,
                $national,
                $price,
                $medicine,
                $suppiles,
                $equipment,
                $dateTime,
                $name,
                $dateTime,
                $name,
                $pricePercent * $medicine,
                $pricePercent * $suppiles,
                $pricePercent * $equipment,
                "Active",
            ];
        }

        return $medsuppilesArray;
    }
    public function collection(Collection $rows)
    {
        $Procedure_Excel  = false;
        $ProcedureInsert  = true;
        $ProcedureSetting = [
            'clinic'            => 'OBS',
            'name'              => 'PAKAWA KAPHONDEE',
            'med_percent'       => 5,
            'supplies_percent'  => 35,
            'equipment_percent' => 60,
        ];

        if ($Procedure_Excel) {
            $procedure   = [];
            $medsuppiles = [];
            // $findLastprocedureID = DB::connection('K2DEV_SUR')->table('m_Procedure')->orderby('ID', 'DESC')->first();
            $findLastprocedureID = DB::connection('K2PROD_SUR')->table('m_Procedure')->orderby('ID', 'DESC')->first();
            $procedureID         = $findLastprocedureID->ID;
            $procedure[]         = [
                "ID",
                "ICD9",
                "ProcedureName",
                "CreateDate",
                "CreateBy",
                "UpdateDate",
                "UpdateBy",
                "Status",
                "ClinicShortName",
            ];
            $medsuppiles[] = [
                "ID",
                "ProcedureID",
                "Nationality",
                "Total",
                "Medicine",
                "MedicalSupplies",
                "Equipment",
                "CreateDate",
                "CreateBy",
                "UpdateDate",
                "UpdateBy",
                "MedicineCost",
                "MedicalSuppiles1Cost",
                "EquipmentCost",
                "Status",
            ];
        }

        foreach ($rows as $rowIndex => $row) {
            if ($rowIndex >= 2 && $row[1] !== null) {
                if ($Procedure_Excel) {
                    $procedureID += 1;
                    $procedureArray = [
                        "ID"              => $procedureID,
                        "ICD9"            => "",
                        "ProcedureName"   => $row[1],
                        "CreateDate"      => date('Y-m-d H:i:s'),
                        "CreateBy"        => $ProcedureSetting['name'],
                        "UpdateDate"      => date('Y-m-d H:i:s'),
                        "UpdateBy"        => $ProcedureSetting['name'],
                        "Status"          => "Active",
                        "ClinicShortName" => $ProcedureSetting['clinic'],
                    ];
                    $procedure[] = $procedureArray;
                } else {
                    $procedureArray = [
                        "ICD9"            => "",
                        "ProcedureName"   => $row[1],
                        "CreateDate"      => date('Y-m-d H:i:s'),
                        "CreateBy"        => $ProcedureSetting['name'],
                        "UpdateDate"      => date('Y-m-d H:i:s'),
                        "UpdateBy"        => $ProcedureSetting['name'],
                        "Status"          => "Active",
                        "ClinicShortName" => $ProcedureSetting['clinic'],
                    ];
                }

                if ($ProcedureInsert) {
                    // dump('Insert :');
                    // $procedureID = 'temp';
                    // dump($procedureArray);

                    // $procedureID = DB::connection('K2DEV_SUR')->table('m_Procedure')->InsertGetId($procedureArray);
                    // $procedureID = DB::connection('K2PROD_SUR')->table('m_Procedure')->InsertGetId($procedureArray);
                }

                $medsuppilesThai = $this->medSuppilesArray($procedureID, $row[2], 'Thai', $ProcedureSetting, $ProcedureInsert);
                $medsuppiles[]   = $medsuppilesThai;

                $medsuppilesInter = $this->medSuppilesArray($procedureID, $row[3], 'Inter', $ProcedureSetting, $ProcedureInsert);
                $medsuppiles[]    = $medsuppilesInter;

                $medsuppilesArab = $this->medSuppilesArray($procedureID, $row[4], 'Arab', $ProcedureSetting, $ProcedureInsert);
                $medsuppiles[]   = $medsuppilesArab;

                if ($ProcedureInsert) {
                    $arrayPriceInsert   = [];
                    $arrayPriceInsert[] = $medsuppilesThai;
                    $arrayPriceInsert[] = $medsuppilesInter;
                    $arrayPriceInsert[] = $medsuppilesArab;

                    // dump('Insert :');
                    // dump($arrayPriceInsert);

                    // DB::connection('K2DEV_SUR')->table('m_MedicalSuppliesInOper')->Insert($arrayPriceInsert);
                    // DB::connection('K2PROD_SUR')->table('m_MedicalSuppliesInOper')->Insert($arrayPriceInsert);
                }
            }
        }

        if ($Procedure_Excel) {
            echo('<script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>');
            echo('<div class="flex gap-6 m-6">');
            echo('<div class="flex-1"><div class="text-center font-bold">Procedure Excel</div>');
            echo('<textarea class="w-full p-3 border h-64">' . json_encode($procedure, JSON_UNESCAPED_UNICODE) . '</textarea>');
            echo('</div>');
            echo('<div class="flex-1"><div class="text-center font-bold">Medsuppiles Excel</div>');
            echo('<textarea class="w-full p-3 border h-64">' . json_encode($medsuppiles, JSON_UNESCAPED_UNICODE) . '</textarea>');
            echo('</div>');
            echo('</div>');
            echo('<a href="https://tableconvert.com/json-to-excel"><div class="text-red-600 text-center">TEXT TO EXCEL</div></a>');
        }

        die();
    }
}
