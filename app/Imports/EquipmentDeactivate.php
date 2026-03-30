<?php
namespace App\Imports;

use App\Services\K2Logger;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;

class EquipmentDeactivate implements ToCollection
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

    public function collection(Collection $rows)
    {
        mb_internal_encoding("UTF-8");
        $clinic = $this->clinic;

        foreach ($rows as $rowIndex => $row) {
            if ($rowIndex >= 1 && $row[0] !== null) {
                // Skip if Name & Code is empty
                if ($row[0] == null && $row[1] == null && $row[2] == null && $row[3] == null && $row[4] == null && $row[5] == null && $row[6] == null && $row[7] == null) {
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
                $type = $row[0];
                $supplier = $row[1];
                $nationality = $row[2];
                $code = $row[3] == null ? 'ไม่ระบุ' : $row[3];
                $name = $row[4] == null ? 'ไม่ระบุ' : mb_substr($row[4], 0, 100);
                $equipment_type = $row[5] == null ? 'ไม่ระบุ' : mb_substr($row[5], 0, 100);

                // Find Active
                $existingRecord = DB::connection($this->environment)->table('m_MedicalSupplies3')
                    ->where('ClinicShortName', $clinic)
                    ->where('Type', $type)
                    ->where('Nationality', $nationality)
                    ->where('Code', $code)
                    ->where('Name', $name)
                    ->where('Supplier', $supplier)  
                    ->where('EquipmentType', $equipment_type)
                    ->where('Status', 'Active')
                    ->first();
                if ($existingRecord) {
                    DB::connection($this->environment)->table('m_MedicalSupplies3')
                        ->where('ID', $existingRecord->ID)
                        ->update([
                            'Status' => 'Inactive',
                        ]);

                        $this->logger->logEquipment($this->clinic, $this->environment, [
                            'type'          => 'Deactivate success!',
                            'supplier'      => $supplier,
                            'equipmentType' => $type,
                            'name'          => $name,
                            'code'          => $code,
                        ]);
                } else {
                    // Not found Reocrd
                    $this->logger->logEquipment($this->clinic, $this->environment, [
                        'type'          => 'Deactivate failed!',
                        'supplier'      => $supplier,
                        'equipmentType' => $type,
                        'name'          => $name,
                        'code'          => $code,
                    ]);
                }
            }
        }

        return;
    }
}
