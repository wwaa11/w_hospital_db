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
        $auth = auth()->user()->name_EN;

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
            "CreateBy"        => $auth,
            "UpdateDate"      => date('Y-m-d H:i:s'),
            "UpdateBy"        => $auth,
            "Status"          => 'Active',
            "ClinicShortName" => $clinic,
        ];
    }

    public function collection(Collection $rows)
    {
        mb_internal_encoding("UTF-8");
        $clinic = $this->clinic;
        foreach ($rows as $rowIndex => $row) {
            if ($rowIndex >= 3 && $row[0] !== null) {
                // Skip if Name & Code is empty
                if ($row[2] == null && $row[3] == null) {
                    $this->logger->skipMed3($this->clinic, $this->environment, [
                        'rowIndex'      => $rowIndex,
                        'supplier'      => $row[0],
                        'equipmentType' => $row[1],
                        'uom'           => $row[4],
                        'priceTH'       => $row[5],
                        'priceInter'    => $row[6],
                        'priceArab'     => $row[7],
                    ]);
                    continue;
                }

                // Set default value if empty
                $code       = $row[3] == null ? 'ไม่ระบุ' : $row[3];
                $name       = mb_substr($row[2], 0, 300);
                $type       = mb_substr($row[1], 0, 100);
                $uom        = $row[4] == null ? 'ไม่ระบุ' : $row[4];
                $priceTH    = $row[5] == null ? 0 : $row[5];
                $priceInter = $row[6] == null ? 0 : $row[6];
                $priceArab  = $row[7] == null ? 0 : $row[7];
                $supplier   = $row[0];

                $arrayPriceInsert   = [];
                $medsuppilesThai    = $this->medSuppiles3Array('Thai', $code, $name, $type, $uom, $priceTH, $supplier, $clinic);
                $arrayPriceInsert[] = $medsuppilesThai;

                $medsuppilesInter   = $this->medSuppiles3Array('Inter', $code, $name, $type, $uom, $priceInter, $supplier, $clinic);
                $arrayPriceInsert[] = $medsuppilesInter;

                $medsuppilesArab    = $this->medSuppiles3Array('Arab', $code, $name, $type, $uom, $priceArab, $supplier, $clinic);
                $arrayPriceInsert[] = $medsuppilesArab;

                // Find Duplicate
                $existingRecord = DB::connection($this->environment)->table('m_MedicalSupplies3')
                    ->where('ClinicShortName', $clinic)
                    ->where('Code', $code)
                    ->where('Name', $name)
                    ->where('Supplier', $supplier)
                    ->where('Status', 'Active')
                    ->first();
                if ($existingRecord) {
                    $updateThai = DB::connection($this->environment)->table('m_MedicalSupplies3')
                        ->where('ClinicShortName', $clinic)
                        ->where('Code', $code)
                        ->where('Name', $name)
                        ->where('Supplier', $supplier)
                        ->where('Status', 'Active')
                        ->where('Nationality', 'Thai')
                        ->update($medsuppilesThai);
                    $updateInter = DB::connection($this->environment)->table('m_MedicalSupplies3')
                        ->where('ClinicShortName', $clinic)
                        ->where('Code', $code)
                        ->where('Name', $name)
                        ->where('Supplier', $supplier)
                        ->where('Status', 'Active')
                        ->where('Nationality', 'Inter')
                        ->update($medsuppilesInter);
                    $updateArab = DB::connection($this->environment)->table('m_MedicalSupplies3')
                        ->where('ClinicShortName', $clinic)
                        ->where('Code', $code)
                        ->where('Name', $name)
                        ->where('Supplier', $supplier)
                        ->where('Status', 'Active')
                        ->where('Nationality', 'Arab')
                        ->update($medsuppilesArab);

                    // Log update
                    $this->logger->logMed3($this->clinic, $this->environment, [
                        'type'          => 'Update',
                        'supplier'      => $supplier,
                        'equipmentType' => $type,
                        'name'          => $name,
                        'code'          => $code,
                        'uom'           => $uom,
                        'thai'          => $priceTH,
                        'inter'         => $priceInter,
                        'arab'          => $priceArab,
                    ]);
                } else {
                    DB::connection($this->environment)->table('m_MedicalSupplies3')->Insert($arrayPriceInsert);
                    // Log insert
                    $this->logger->logMed3($this->clinic, $this->environment, [
                        'type'          => 'Insert',
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
        }

        return;
    }
}
