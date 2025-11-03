<?php
namespace App\Imports;

use App\Services\K2Logger;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;

class Med3Deactivate implements ToCollection
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
                if ($row[0] == null && $row[2] == null && $row[3] == null) {
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
                $supplier = $row[0];
                $type     = mb_substr($row[1], 0, 100);
                $name     = mb_substr($row[2], 0, 300);
                $code     = $row[3] == null ? 'ไม่ระบุ' : $row[3];

                // Find Duplicate
                $existingRecord = DB::connection($this->environment)->table('m_MedicalSupplies3')
                    ->where('ClinicShortName', $clinic)
                    ->where('Code', $code)
                    ->where('Name', $name)
                    ->where('Supplier', $supplier)
                    ->where('Status', 'Active')
                    ->get();
                if (count($existingRecord) > 0) {
                    foreach ($existingRecord as $record) {
                        DB::connection($this->environment)->table('m_MedicalSupplies3')
                            ->where('ID', $record->ID)
                            ->update([
                                'Status' => 'Inactive',
                            ]);

                        $this->logger->logMed3($this->clinic, $this->environment, [
                            'type'          => 'Deactivate success!',
                            'supplier'      => $supplier,
                            'equipmentType' => $type,
                            'name'          => $name,
                            'code'          => $code,
                        ]);
                    }
                } else {
                    // Not found Reocrd
                    $this->logger->logMed3($this->clinic, $this->environment, [
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
