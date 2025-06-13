<?php
namespace App\Imports;

use App\Services\K2Logger;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;

class Procedure implements ToCollection
{
    protected $clinic;
    protected $environment;
    protected $logger;

    public function __construct($clinic, $environment = 'K2DEV_SUR')
    {
        $this->clinic      = $clinic;
        $this->environment = $environment;
        $this->logger      = new K2Logger();
    }

    public function medSuppilesArray($id, $price, $national, $setting)
    {
        // Clean the price string: remove commas and dots
        $cleanedPrice = str_replace(['.', ','], '', $price);
        if (strpos($cleanedPrice, '-') !== false) {
            // If it contains a hyphen, remove spaces and split
            $priceRange = str_replace(' ', '', $cleanedPrice);
            $priceParts = explode('-', $priceRange);

            // Ensure we have two parts and they are numeric
            if (count($priceParts) === 2 && is_numeric($priceParts[0]) && is_numeric($priceParts[1])) {
                $priceMin = (float) $priceParts[0];
                $priceMax = (float) $priceParts[1];
                // Calculate the new price using the formula
                $finalPrice = $priceMin + ($priceMax - $priceMin) * 0.8;
            } else {
                                 // Handle cases where the hyphenated format is unexpected
                                 // For now, we'll set price to 0 or handle as an error
                $finalPrice = 0; // Or throw an exception, log an error, etc.
                                 // You might want to add logging here to see which price caused this issue
                                 // \Log::warning("Unexpected price format with hyphen: " . $price);
            }
        } else {
            // If no hyphen, use the cleaned price directly (ensure it's numeric)
            $finalPrice = is_numeric($cleanedPrice) ? (float) $cleanedPrice : 0;
            // You might want to add logging here if $cleanedPrice is not numeric
            // if (!is_numeric($cleanedPrice)) {
            //     \Log::warning("Non-numeric price without hyphen: " . $price);
            // }
        }

        $name         = $setting['name'];
        $pricePercent = $finalPrice / 100;
        $medicine     = $setting['med_percent'];
        $suppiles     = $setting['supplies_percent'];
        $equipment    = $setting['equipment_percent'];
        $dateTime     = date('Y-m-d H:i:s');

        $medsuppilesArray = [
            "ProcedureID"          => $id,
            "Nationality"          => $national,
            "Total"                => $finalPrice,
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

        return $medsuppilesArray;
    }

    public function collection(Collection $rows)
    {
        $ProcedureSetting = [
            'clinic'            => $this->clinic,
            'name'              => 'PAKAWA KAPHONDEE',
            'med_percent'       => 5,
            'supplies_percent'  => 35,
            'equipment_percent' => 60,
        ];

        foreach ($rows as $rowIndex => $row) {
            if ($rowIndex >= 2 && $row[1] !== null) {
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

                $procedureID = 'temp';
                if ($this->environment == 'K2DEV_SUR') {
                    $procedureID = DB::connection('K2DEV_SUR')->table('m_Procedure')->InsertGetId($procedureArray);
                } else {
                    $procedureID = DB::connection('K2PROD_SUR')->table('m_Procedure')->InsertGetId($procedureArray);
                }

                $medsuppilesThai = $this->medSuppilesArray($procedureID, $row[2], 'Thai', $ProcedureSetting);
                $medsuppiles[]   = $medsuppilesThai;

                $medsuppilesInter = $this->medSuppilesArray($procedureID, $row[3], 'Inter', $ProcedureSetting);
                $medsuppiles[]    = $medsuppilesInter;

                $medsuppilesArab = $this->medSuppilesArray($procedureID, $row[4], 'Arab', $ProcedureSetting);
                $medsuppiles[]   = $medsuppilesArab;

                $arrayPriceInsert   = [];
                $arrayPriceInsert[] = $medsuppilesThai;
                $arrayPriceInsert[] = $medsuppilesInter;
                $arrayPriceInsert[] = $medsuppilesArab;

                // Log the data before insertion
                $this->logger->logProcedure($this->clinic, $this->environment, [
                    'procedure' => $procedureArray,
                    'thai'      => $medsuppilesThai,
                    'inter'     => $medsuppilesInter,
                    'arab'      => $medsuppilesArab,
                ]);

                if ($this->environment == 'K2DEV_SUR') {
                    DB::connection('K2DEV_SUR')->table('m_MedicalSuppliesInOper')->Insert($arrayPriceInsert);
                } else {
                    DB::connection('K2PROD_SUR')->table('m_MedicalSuppliesInOper')->Insert($arrayPriceInsert);
                }
            }
        }

        return;
    }
}
