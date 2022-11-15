<?php

namespace App\Http\Controllers\pages;

use Illuminate\Http\Request;

class AccountSettingsSecurity extends MyController
{
  public function index()
  {
    $pageConfigs = ['myLayout' => 'horizontal'];
    return view('content.pages.pages-account-settings-security', ['pageConfigs'=> $pageConfigs]);
  }
}
