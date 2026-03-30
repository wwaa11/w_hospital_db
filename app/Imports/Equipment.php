<?php

namespace App\Imports;

use App\Services\K2Logger;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;

class Equipment implements ToCollection
{
    protected $clinic;

    protected $environment;

    protected $logger;

    public function __construct($clinic, $environment)
    {
        $this->clinic = $clinic;
        $this->environment = $environment;
        $this->logger = new K2Logger;
    }

    public function equipmentArray($type, $supplier, $nationality, $code, $name, $equipment_type, $price, $uom, $clinic)
    {
        $auth = auth()->user()->name_EN;

        return [
            'Nationality' => $nationality,
            'Code' => $code,
            'Name' => $name,
            'Type' => $type,
            'EquipmentType' => $equipment_type,
            'UOM' => $uom,
            'Price' => $price,
            'Supplier' => $supplier,
            'CreateDate' => date('Y-m-d H:i:s'),
            'CreateBy' => $auth,
            'UpdateDate' => date('Y-m-d H:i:s'),
            'UpdateBy' => $auth,
            'Status' => 'Active',
            'ClinicShortName' => $clinic,
        ];
    }

    public function collection(Collection $rows)
    {
        mb_internal_encoding('UTF-8');
        $clinic = $this->clinic;
        foreach ($rows as $rowIndex => $row) {
            if ($rowIndex >= 1 && $row[0] !== null) {
                // skip if nationality is empty
                if($row[2] == null) {
                    $this->logger->skipEquipment($this->clinic, $this->environment, [
                        'rowIndex' => $rowIndex,
                        'name'
                    ]);
                    continue;
                }
                // Skip if Name & Code is empty
                if ($row[2] == null && $row[3] == null) {
                    $this->logger->skipEquipment($this->clinic, $this->environment, [
                        'rowIndex' => $rowIndex,
                    ]);

                    continue;
                }

                // Set default value if empty
                $type = $row[0];
                $supplier = $row[1];
                $nationality = $row[2];
                $code = $row[3] == null ? 'ไม่ระบุ' : $row[3];
                $name = $row[4] == null ? 'ไม่ระบุ' : mb_substr($row[4], 0, 100);
                $equipment_type = $row[5] == null ? 'ไม่ระบุ' : mb_substr($row[5], 0, 100);
                $price = $row[6] == null ? 0 : $row[6];
                $uom = $row[7] == null ? 'ไม่ระบุ' : $row[7];

                $arrayPriceInsert = [];
                $equipmentArray = $this->equipmentArray($type, $supplier, $nationality, $code, $name, $equipment_type, $price, $uom, $clinic);
                $arrayPriceInsert[] = $equipmentArray;

                // Find Duplicate
                $existingRecord = DB::connection($this->environment)->table('m_MedicalSupplies3')
                    ->where('ClinicShortName', $clinic)
                    ->where('Code', $code)
                    ->where('Name', $name)
                    ->where('Supplier', $supplier)
                    ->where('Nationality', $nationality)
                    ->where('Status', 'Active')
                    ->first();

                if ($existingRecord) {
                    $updateEquipment = DB::connection($this->environment)->table('m_MedicalSupplies3')
                        ->where('ClinicShortName', $clinic)
                        ->where('Code', $code)
                        ->where('Name', $name)
                        ->where('Supplier', $supplier)
                        ->where('Nationality', $nationality)
                        ->where('Status', 'Active')
                        ->update($equipmentArray);

                    // Log update
                    $this->logger->logEquipment($this->clinic, $this->environment, [
                        'type' => 'Update',
                        'supplier' => $supplier,
                        'nationality' => $nationality,
                        'type' => $type,
                        'equipmentType' => $equipment_type,
                        'name' => $name,
                        'code' => $code,
                        'price' => $price,
                        'uom' => $uom,
                    ]);
                } else {
                    DB::connection($this->environment)->table('m_MedicalSupplies3')->Insert($arrayPriceInsert);
                    // Log insert
                    $this->logger->logEquipment($this->clinic, $this->environment, [
                        'type' => 'Insert',
                        'supplier' => $supplier,
                        'nationality' => $nationality,
                        'type' => $type,
                        'equipmentType' => $equipment_type,
                        'name' => $name,
                        'code' => $code,
                        'price' => $price,
                        'uom' => $uom,
                    ]);
                }
            }
        }

    }
}
