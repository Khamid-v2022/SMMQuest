<?php

namespace App\Http\Controllers\pages;

use Illuminate\Http\Request;

class HomePage extends MyController
{
  public function index()
  {
    $pageConfigs = ['myLayout' => 'horizontal'];

    return view('content.pages.pages-home', ['pageConfigs'=> $pageConfigs]);
  }
}
