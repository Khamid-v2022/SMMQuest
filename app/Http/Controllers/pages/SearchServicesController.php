<?php

namespace App\Http\Controllers\pages;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Models\UserProvider;
use App\Models\Service;

class SearchServicesController extends MyController
{
    public function index()
    {
        $pageConfigs = ['myLayout' => 'horizontal'];

        $providers = UserProvider::with(['provider'])
          ->where('user_id', Auth::user()->id)->get();
        $types = Service::service_types(Auth::user()->id);

        return view('content.pages.pages-search-services', [
            'pageConfigs'=> $pageConfigs, 
            'providers' => $providers,
            'types' => $types
        ]);
    }

    public function searchServices(Request $request){
        $providers = $request->providers;
        $min = $request->min;
        $max = $request->max;
        $type = $request->type;
        $include = $request->include;
        $exclude = $request->exclude;

        $result = Service::search_services(
            Auth::user()->id,
            $providers,
            $type,
            $include,
            $exclude,
            $min,
            $max
        );

        return response()->json(['code'=>200, 'services'=>$result], 200);
    }

}
