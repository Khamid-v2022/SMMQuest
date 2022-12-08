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

        // $providers = UserProvider::with(['provider'])
        //   ->where('user_id', Auth::user()->id)->get();
        $providers = UserProvider::getProviderList(Auth::user()->id);
        $types = Service::service_types_with_query(Auth::user()->id);
        $types_val=[];
        
        foreach( $types as $type ){
            array_push($types_val, $type->type);
        }
        // Default as first position
        if(in_array("Default", $types_val)){
            $index = array_search("Default", $types_val);
            unset($types_val[$index]);
            array_unshift($types_val, "Default");
        }

        return view('content.pages.pages-search-services', [
            'pageConfigs'=> $pageConfigs, 
            'container' => 'container-fluid',
            'providers' => $providers,
            'types' => $types_val
        ]);
    }

    public function searchServices(Request $request){
        ini_set('memory_limit', '2048M');

        $providers = $request->providers;
        $min = $request->min;
        $max = $request->max;
        $type = $request->type;
        $include = $request->include;
        $exclude = $request->exclude;
        

        $result = Service::search_services_with_query(
            Auth::user()->id,
            $providers,
            $type,
            $include,
            $exclude,
            $min,
            $max,
            $request->min_rate,
            $request->max_rate
        );

        return response()->json(['code'=>200, 'services'=>$result], 200);
    }

}
