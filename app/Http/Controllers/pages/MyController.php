<?php

namespace App\Http\Controllers\pages;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Support\Facades\Auth;

class MyController extends Controller
{
    protected $user;
    
    public function __construct(){
        parent::__construct();

        $this->middleware(function ($request, $next) {
            $this->user = Auth::user();
            return $next($request);
        });
    }

}