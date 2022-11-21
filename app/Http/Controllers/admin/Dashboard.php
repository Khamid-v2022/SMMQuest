<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class Dashboard extends Controller
{
  public function index()
  {
    $pageConfigs = ['myLayout' => 'horizontal'];

    return view('content.adminside.dashboard', ['pageConfigs'=> $pageConfigs]);
  }
}