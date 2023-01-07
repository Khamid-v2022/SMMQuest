<?php

namespace App\Http\Controllers\pages;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Models\UserProvider;
use App\Models\Service;
use App\Models\Provider;
use App\Models\Currency;
use App\Models\UserList;
use App\Models\ListService;

class SearchServicesController extends MyController
{
    private $currencies = NULL;

    public function index()
    {
        $pageConfigs = ['myLayout' => 'horizontal'];

        $this->currencies = Currency::where("id", 1)->first();
       
        
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
        set_time_limit(300);

        $providers = $request->providers;
        $min = $request->min;
        $max = $request->max;
        $type = $request->type;
        $include = $request->include;
        $exclude = $request->exclude;
        $added_after = $request->added_after;
        $added_before = $request->added_before;
        

        $res = Service::search_services_with_query(
            Auth::user()->id,
            $providers,
            $type,
            $include,
            $exclude,
            $min,
            $max,
            $request->min_rate,
            $request->max_rate,
            $added_after,
            $added_before
        );
        $result = $res['result'];
        $query = $res['sql_query'];


        if(count($result) > 150000){
            return response()->json(['code'=>401, 'message'=>"There are too many results.
            Please refine your search a bit more", 'result_count' => count($result)], 200);
        }

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
                }
                    
            }
            
            $price = array_column($result, 'rate');
            array_multisort($price, SORT_ASC, $result);
        }

        return response()->json(['code'=>200, 'services'=>$result, 'query'=> $query], 200);
    }

    public function loadExistingList(){
        $existing_list = UserList::where('user_id', Auth::user()->id)->where('is_started', '0')->get();
        return response()->json(['code'=>200, 'existing_list'=>$existing_list], 200);
    }

    public function createNewList(Request $request){
        $service_ids = $request->selected_service_ids;
        $list_name = $request->list_name;
  
        // check list name is exist alredy
        $exist = UserList::where("list_name", $list_name)->get();
        if(count($exist) > 0){
            return response()->json(['code'=>400, 'message'=>'List name already exists. Please enter a different name.'], 200);
        }

        // create new list
        $list = UserList::create([
            'user_id' => Auth::user()->id,
            'list_name' => $list_name
        ]);

        $data = [];

        foreach($service_ids as $id){
            // get API template info from service id
            $service = Service::where('id', $id)->first();
            $provider = Provider::where('id', $service->provider_id)->first();
            array_push($data, ['list_id' => $list->id, 'service_id' => $id, 'api_template' => $provider->api_template, 'created_at' => date('Y-m-d H:i:s')]);
        }
        ListService::insert($data);
        return response()->json(['code'=>200, 'message'=>'success'], 200);
    }

    public function addServicesExistingList(Request $request){
        $service_ids = $request->selected_service_ids;
        $data = [];

        // foreach($service_ids as $id){
        //     array_push($data, ['list_id' => $request->selected_list_id, 'service_id' => $id]);
        // }
        // ListService::insert($data);
        $added_count = 0;
        foreach($service_ids as $id){
            $exist = ListService::where('list_id', $request->selected_list_id)->where('service_id', $id)->get();
            if(count($exist) == 0){
                $added_count++;
                $service = Service::where('id', $id)->first();
                $provider = Provider::where('id', $service->provider_id)->first();
                ListService::create(['list_id' => $request->selected_list_id, 'service_id' => $id, 'api_template' => $provider->api_template]);
            }
        }

        if($added_count > 0)
            return response()->json(['code'=>200, 'message'=>$added_count . ' services added to the existing list'], 200);
        else
            return response()->json(['code'=>400, 'message'=>'The services you have selected already exist in the selected list.'], 200);
    }

}
