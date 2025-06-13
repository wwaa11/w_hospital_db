<?php
namespace App\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class K2Logger
{
    protected $logPath;
    protected $dateFormat = 'Y-m-d H:i:s';

    public function __construct()
    {
        $this->logPath = 'logs/k2';
    }

    public function logMed3($clinic, $environment, $data)
    {
        $logData = [
            'timestamp'   => now()->format($this->dateFormat),
            'user'        => Auth::user()->name ?? 'System',
            'clinic'      => $clinic,
            'environment' => $environment,
            'type'        => 'MED3',
            'data'        => $data,
        ];

        $this->writeLog($logData);
    }

    public function logProcedure($clinic, $environment, $data)
    {
        $logData = [
            'timestamp'   => now()->format($this->dateFormat),
            'user'        => Auth::user()->name ?? 'System',
            'clinic'      => $clinic,
            'environment' => $environment,
            'type'        => 'PROCEDURE',
            'data'        => $data,
        ];

        $this->writeLog($logData);
    }

    protected function writeLog($logData)
    {
        $date     = now()->format('Y-m-d');
        $filename = "{$this->logPath}/{$date}.log";

        $logEntry = json_encode($logData, JSON_PRETTY_PRINT) . "\n" . str_repeat('-', 80) . "\n";

        Storage::append($filename, $logEntry);
    }
}
