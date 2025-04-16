<?php
namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class Med3 implements ToCollection
{
    /**
     * @param Collection $collection
     */
    public function collection(Collection $rows)
    {
        $data    = [];
        $supplie = '';
        foreach ($rows as $rowIndex => $row) {
            if ($rowIndex == 5) {
                dump($row);
            }
            if ($rowIndex >= 3 && $row[2] !== null) {
                if ($row[0] !== null) {
                    $supplie = $row[0];
                }
                $data[] = [
                    'Equipment',
                    $supplie,
                    'Thai',
                    $row[3],
                    $row[2],
                    $row[1],
                    $row[5],
                    $row[4],
                ];
                $data[] = [
                    'Equipment',
                    $supplie,
                    'Inter',
                    $row[3],
                    $row[2],
                    $row[1],
                    $row[6],
                    $row[4],
                ];
                $data[] = [
                    'Equipment',
                    $supplie,
                    'Arab',
                    $row[3],
                    $row[2],
                    $row[1],
                    $row[7],
                    $row[4],
                ];
            }
        }
        dd(json_encode($data, JSON_UNESCAPED_UNICODE));

        return $data;
    }
}
