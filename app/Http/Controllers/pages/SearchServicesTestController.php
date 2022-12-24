<?php

namespace App\Http\Controllers\pages;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Models\UserProvider;
use App\Models\Service;
use App\Models\Currency;

class SearchServicesTestController extends MyController
{
    private $currencies = NULL;

    public function index()
    {
        $pageConfigs = ['myLayout' => 'horizontal'];

        $this->currencies = Currency::where("id", 1)->first();

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

        return view('content.pages.pages-search-services-test', [
            'pageConfigs'=> $pageConfigs, 
            'container' => 'container-fluid',
            'providers' => $providers,
            'types' => $types_val
        ]);
    }

    public function searchServices(Request $request){
        ini_set('memory_limit', '2048M');
        set_time_limit(300);

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
            $request->max_rate,
        );



        $tbody_html = '';

        if(count($result) > 0){
            if(!$this->currencies){
                // currency table based USD
                $this->currencies = Currency::where("id", 1)->first();
            }
            
            for($index = 0; $index < count($result); $index++){
                $currency = NULL;
                if ($result[$index]->user_currency){
                    if(strtoupper($result[$index]->user_currency) != $request->currency){
                        $currency = $this->currencies[strtoupper($result[$index]->user_currency)];
                    }
                } else if($result[$index]->main_currency){
                    if(strtoupper($result[$index]->main_currency) != $request->currency){
                        $currency = $this->currencies[strtoupper($result[$index]->main_currency)];
                    }
                }

                if($currency && $currency != 0) {
                    $result[$index]->rate = ($result[$index]->rate / $currency) * $this->currencies[$request->currency];

                    $result[$index]->rate = '≈ ' . $result[$index]->rate;
                }
                
                $item = $result[$index];
                // if($index > 100){
                //     $tbody_html .= "<tr style='display:none'>";
                //     // break;
                // }
                // else 
                    $tbody_html .= "<tr>";
                $tbody_html .= "<td>" . $item->domain . "</td>";
                $tbody_html .= "<td>" . $item->category . "</td>";
                $tbody_html .= "<td>" . $item->service . "</td>";
                $tbody_html .= "<td>" . $item->name . "</td>";
                $tbody_html .= "<td>" . $item->type . "</td>";
                $tbody_html .= "<td>" . $item->rate . "</td>";
                $tbody_html .= "<td>" . $item->min . "</td>";
                $tbody_html .= "<td>" . $item->max . "</td>";
                $tbody_html .= "<td>" . $item->dripfeed . "</td>";
                $tbody_html .= "<td>" . $item->refill . "</td>";
                $tbody_html .= "<td>" . $item->cancel . "</td>";
                // $tbody_html .= "<td>" . $item->is_favorite . "</td>";
                $tbody_html .= "</tr>";
            }
        }

        return response()->json(['code'=>200, "tbody" => $tbody_html], 200);
    }

}
