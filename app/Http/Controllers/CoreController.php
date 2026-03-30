<?php

namespace App\Http\Controllers;

use DB;

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
