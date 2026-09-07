<?php

namespace App\Http\Controllers;

use DB;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class CoreController extends Controller
{
    public function __construct()
    {
        mb_internal_encoding('UTF-8');
    }

    public function index()
    {
        return view('layouts.index');
    }

    public function random()
    {
        $defaultFile = 'รายชื่อสมาชิกสหกรณ์ ประชุมใหญ่ กค.69.xlsx';
        $defaultExists = file_exists(public_path($defaultFile));
        $names = session('random_names', []);
        $history = session('random_history', []);
        $drawnKeys = session('random_drawn_keys', []);

        $remaining = array_values(array_filter($names, function ($person) use ($drawnKeys) {
            return ! in_array($this->randomPersonKey($person), $drawnKeys, true);
        }));

        return view('random', [
            'defaultFile' => $defaultFile,
            'defaultExists' => $defaultExists,
            'names' => $names,
            'history' => $history,
            'drawnKeys' => $drawnKeys,
            'remaining' => $remaining,
            'latestRound' => $history[0] ?? null,
        ]);
    }

    public function randomUpload(Request $request)
    {
        $defaultFile = 'รายชื่อสมาชิกสหกรณ์ ประชุมใหญ่ กค.69.xlsx';
        $defaultExists = file_exists(public_path($defaultFile));

        $request->validate([
            'file' => 'nullable|file|mimes:xlsx,xls',
            'use_default' => 'nullable|boolean',
        ]);

        $useDefault = $request->boolean('use_default') || ! $request->hasFile('file');

        if ($request->hasFile('file')) {
            $rows = Excel::toArray([], $request->file('file'));
            $source = $request->file('file')->getClientOriginalName();
        } elseif ($useDefault && $defaultExists) {
            $rows = Excel::toArray([], public_path($defaultFile));
            $source = $defaultFile;
        } else {
            return back()->withErrors(['file' => 'กรุณาอัปโหลดไฟล์ Excel หรือเลือกใช้ไฟล์เริ่มต้น']);
        }

        $sheet = $rows[0] ?? [];
        $names = [];

        foreach ($sheet as $index => $row) {
            if ($index === 0) {
                continue;
            }

            $seq = trim((string) ($row[0] ?? ''));
            $code = trim((string) ($row[1] ?? ''));
            $name = trim((string) ($row[2] ?? ''));

            if ($name === '' || $name === 'ชื่อ - นามสกุล') {
                continue;
            }

            $names[] = [
                'seq' => $seq,
                'code' => $code,
                'name' => $name,
            ];
        }

        if (count($names) === 0) {
            return back()->withErrors(['file' => 'ไม่พบรายชื่อในไฟล์ Excel']);
        }

        session([
            'random_names' => $names,
            'random_source' => $source,
            'random_history' => [],
            'random_drawn_keys' => [],
        ]);

        return redirect()->route('random')->with('success', 'โหลดรายชื่อสำเร็จ '.count($names).' คน');
    }

    public function randomPick(Request $request)
    {
        $names = session('random_names', []);
        $history = session('random_history', []);
        $drawnKeys = session('random_drawn_keys', []);

        if (count($names) === 0) {
            return back()->withErrors(['count' => 'กรุณาอัปโหลดรายชื่อก่อนทำการสุ่ม']);
        }

        $remaining = array_values(array_filter($names, function ($person) use ($drawnKeys) {
            return ! in_array($this->randomPersonKey($person), $drawnKeys, true);
        }));

        if (count($remaining) === 0) {
            return back()->withErrors(['count' => 'สุ่มครบทุกรายชื่อแล้ว ไม่มีชื่อเหลือให้สุ่ม']);
        }

        $request->validate([
            'count' => 'required|integer|min:1|max:'.count($remaining),
        ], [
            'count.max' => 'สุ่มได้สูงสุด '.count($remaining).' คน (เหลือในรายการ)',
        ]);

        $count = (int) $request->input('count');
        $pool = $remaining;
        shuffle($pool);
        $results = array_slice($pool, 0, $count);

        foreach ($results as $person) {
            $drawnKeys[] = $this->randomPersonKey($person);
        }

        array_unshift($history, [
            'round' => count($history) + 1,
            'drawn_at' => now()->format('H:i:s d/m/Y'),
            'count' => $count,
            'people' => $results,
        ]);

        session([
            'random_history' => $history,
            'random_drawn_keys' => array_values(array_unique($drawnKeys)),
        ]);

        return redirect()->route('random');
    }

    public function randomClear()
    {
        session()->forget([
            'random_names',
            'random_source',
            'random_history',
            'random_drawn_keys',
            'random_results',
            'random_count',
        ]);

        return redirect()->route('random');
    }

    public function randomClearHistory()
    {
        session([
            'random_history' => [],
            'random_drawn_keys' => [],
        ]);

        return redirect()->route('random')->with('success', 'ล้างประวัติการสุ่มแล้ว สามารถสุ่มรายชื่อซ้ำได้');
    }

    private function randomPersonKey(array $person): string
    {
        $code = trim((string) ($person['code'] ?? ''));
        if ($code !== '') {
            return 'code:'.$code;
        }

        return 'name:'.trim((string) ($person['name'] ?? '')).'|'.trim((string) ($person['seq'] ?? ''));
    }

    public function query()
    {
        $totalActiveHN = DB::connection('SSB')
            ->table('HNPAT_INFO')
            ->whereNull('FileDeletedDate')
            ->select('JobDescription')
            ->get();

        $totalActiveHNCount = $totalActiveHN->count();

        $totalPDPA1 = $totalActiveHN->where('JobDescription', '1')->count();
        $totalPDPA3 = $totalActiveHN->where('JobDescription', '3')->count();

        dump('Total HN : '.$totalActiveHNCount);
        dump('Total PDPA Confirm : '.$totalPDPA1 + $totalPDPA3);
        dump('Total Percent : '.(($totalPDPA1 + $totalPDPA3) / $totalActiveHNCount) * 100);
        exit();

        return view('query', compact('datas'));
    }
}
