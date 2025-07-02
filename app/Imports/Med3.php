<?php
namespace App\Imports;

use App\Services\K2Logger;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;

class Med3 implements ToCollection
{
    protected $clinic;
    protected $environment;
    protected $logger;

    public function __construct($clinic, $environment)
    {
        $this->clinic      = $clinic;
        $this->environment = $environment;
        $this->logger      = new K2Logger();
    }

    public function medSuppiles3Array($national, $code, $name, $type, $uom, $price, $supplier, $clinic)
    {
        return [
            "Nationality"     => $national,
            "Code"            => $code,
            "Name"            => $name,
            "Type"            => 'Equipment',
            "EquipmentType"   => $type,
            "UOM"             => $uom,
            "Price"           => $price,
            "Supplier"        => $supplier,
            "CreateDate"      => date('Y-m-d H:i:s'),
            "CreateBy"        => 'PAKAWA KAPHONDEE',
            "UpdateDate"      => date('Y-m-d H:i:s'),
            "UpdateBy"        => 'PAKAWA KAPHONDEE',
            "Status"          => 'Active',
            "ClinicShortName" => $clinic,
        ];
    }

    public function collection(Collection $rows)
    {
        $clinic = $this->clinic;

        foreach ($rows as $rowIndex => $row) {
            if ($rowIndex > 2 && $row[0] !== null) {
                $code       = $row[3];
                $name       = $row[2];
                $type       = $row[1];
                $uom        = $row[4];
                $priceTH    = $row[5];
                $priceInter = $row[6];
                $priceArab  = $row[7];
                $supplier   = $row[0];

                $arrayPriceInsert   = [];
                $medsuppilesThai    = $this->medSuppiles3Array('Thai', $code, $name, $type, $uom, $priceTH, $supplier, $clinic);
                $arrayPriceInsert[] = $medsuppilesThai;

                $medsuppilesInter   = $this->medSuppiles3Array('Inter', $code, $name, $type, $uom, $priceInter, $supplier, $clinic);
                $arrayPriceInsert[] = $medsuppilesInter;

                $medsuppilesArab    = $this->medSuppiles3Array('Arab', $code, $name, $type, $uom, $priceArab, $supplier, $clinic);
                $arrayPriceInsert[] = $medsuppilesArab;

                if ($this->environment == 'K2DEV_SUR') {
                    DB::connection('K2DEV_SUR')->table('m_MedicalSupplies3')->Insert($arrayPriceInsert);
                } else {
                    DB::connection('K2PROD_SUR')->table('m_MedicalSupplies3')->Insert($arrayPriceInsert);
                }

                // Log the data before insertion
                $this->logger->logMed3($this->clinic, $this->environment, [
                    'supplier'      => $supplier,
                    'equipmentType' => $type,
                    'name'          => $name,
                    'code'          => $code,
                    'uom'           => $uom,
                    'thai'          => $priceTH,
                    'inter'         => $priceInter,
                    'arab'          => $priceArab,
                ]);
            }
        }

        return;
    }
}
