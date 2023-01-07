<?php

namespace App\Http\Controllers\pages;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentController extends MyController
{
    public function index()
    {
        $pageConfigs = ['myLayout' => 'horizontal'];

        
        return view('content.pages.pages-payment', [
            'pageConfigs'=> $pageConfigs, 
        ]);
            
    }
}