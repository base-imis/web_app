<?php

namespace App\Http\Controllers\Containmentmngmtinfo;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ContainmentManagementController extends Controller
{
    //
    public function index()
    {
        $page_title = "Standard Septic Tank Monitoring Dashboard";
        return view('containmentmanagementinfo.index', compact('page_title'));
    }
}
