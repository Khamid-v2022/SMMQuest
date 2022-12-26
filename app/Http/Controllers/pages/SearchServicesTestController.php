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
        $min_sel_html = $max_sel_html = $service_type_html = '<option value="-1">All</option>';
        $providers_html = '<option value="-1">All</option><option value="0">Favorite Providers Only</option>';
        
        if(count($result) > 0){
            if(!$this->currencies){
                // currency table based USD
                $this->currencies = Currency::where("id", 1)->first();
            }
            
            $min_opts = [];
            $max_opts = [];
            $providers_opts = [];
            $types_opts = [];

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
                }
                
                $item = $result[$index];
                // if($index > 100){
                //     $tbody_html .= "<tr style='display:none'>";
                //     // break;
                // }
                // else 
                    $tbody_html .= "<tr>";
                
                if($item->is_favorite == 1)
                    $tbody_html .= "<td>" . $item->domain . "<i class='bx bxs-like text-warning ms-1' style='display:inline'></i></td>";
                else 
                    $tbody_html .= "<td>" . $item->domain . "</td>";
                
                $tbody_html .= "<td>" . $item->category . "</td>";
                $tbody_html .= "<td>" . $item->service . "</td>";
                $tbody_html .= "<td>" . $item->name . "</td>";
                $tbody_html .= "<td>" . $item->type . "</td>";
                $tbody_html .= "<td>" . ($item->rate ? number_format($item->rate, 5, '.', ',') : "") . "</td>";
                $tbody_html .= "<td>" . number_format($item->min, 0, '.', ',') . "</td>";
                $tbody_html .= "<td>" . number_format($item->max, 0, '.', ',') . "</td>";
                $tbody_html .= "<td>" . ($item->dripfeed == 1 ? '<span class="badge bg-label-success">Yes</span>' : '<span class="badge bg-label-warning">No</span>') . "</td>";
                $tbody_html .= "<td>" . ($item->refill == 1 ? '<span class="badge bg-label-success">Yes</span>' : '<span class="badge bg-label-warning">No</span>') . "</td>";
                $tbody_html .= "<td>" . ($item->cancel == 1 ? '<span class="badge bg-label-success">Yes</span>' : '<span class="badge bg-label-warning">No</span>') . "</td>";
                $tbody_html .= "<td>" . $item->is_favorite . "</td>";
                $tbody_html .= "</tr>";


                // min_opt/max_opt/providers_opt/types_opt
                if(!in_array($item->domain, $providers_opts)){
                    array_push($providers_opts, $item->domain);
                }

                if(!in_array($item->type, $types_opts)){
                    array_push($types_opts, $item->type);
                }

                if(!in_array($item->min, $min_opts)){
                    array_push($min_opts, $item->min);
                }

                if(!in_array($item->max, $max_opts)){
                    array_push($max_opts, $item->max);
                }

            }

            sort($min_opts);
            sort($max_opts);

            
            
            foreach($min_opts as $item){
                $min_sel_html .= '<option value="' . number_format($item, 0, '.', ',') . '">' . number_format($item, 0, '.', ',') . '</option>';
            }

            foreach($max_opts as $item){
                $max_sel_html .= '<option value="' . number_format($item, 0, '.', ',') . '">' . number_format($item, 0, '.', ',') . '</option>';
            }

            foreach($providers_opts as $item){
                $providers_html .= '<option value="' . $item . '">' . $item . '</option>';
            }

            $types_opts = array_merge(array('Default'), array_diff($types_opts, array('Default')));
            foreach($types_opts as $item){
                $service_type_html .= '<option value="' . $item . '">' . $item . '</option>';
            }

        }

        return response()->json([
                    'code'=>200, 
                    "tbody" => $tbody_html, 
                    "provider_opt_html" => $providers_html, 
                    "type_opt_html" => $service_type_html, 
                    "min_opt_html"=> $min_sel_html,
                    "max_opt_html"=> $max_sel_html
                ], 200);
    }

}
