<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

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
}
