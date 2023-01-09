<?php

namespace App\Http\Controllers\pages;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\UserList;
use App\Models\ListService;
use App\Models\Currency;

class MyListController extends MyController
{
    private $currencies = NULL;

    public function index()
    {
        $pageConfigs = ['myLayout' => 'horizontal'];
        $this->currencies = Currency::where("id", 1)->first();

        if(Auth::user()->subscrib_status == 0){
            // get my list
            // $list = UserList::where('user_id', Auth::user()->id)
            //             ->with(['services'])
            //             ->orderBy('created_at', 'DESC')
            //             ->get();
            // return view('content.pages.pages-my-list-non-subscrib', [
            //     'pageConfigs'=> $pageConfigs, 
            //     'list' => $list
            // ]);
        } else {

            return view('content.pages.pages-my-list-subscrib', [
                'pageConfigs'=> $pageConfigs
            ]);
        }
    }

    public function loadMyLists(Request $request){
            // using Eloquent 
            // $list = UserList::where('user_id', Auth::user()->id)
            //             ->with(['services'])
            //             ->orderBy('created_at', 'DESC')
            //             ->get();

            // Using SQL query
            $result = UserList::getMyLists(Auth::user()->id);
            $list = $result['result'];


            $return_result = [];

            if(count($list) > 0){
                if(!$this->currencies){
                    // currency table based USD
                    $this->currencies = Currency::where("id", 1)->first();
                }

                for($index = 0; $index < count($list); $index++){
                    $currency = NULL;
                    if(strtoupper($list[$index]->balance_currency) != $request->currency){
                        $currency = $this->currencies[strtoupper($list[$index]->balance_currency)];
                    }

                    if($currency && $currency != 0) {
                        $list[$index]->rate = round(($list[$index]->rate / $currency) * $this->currencies[$request->currency], 6);
                    }

                    // group by list
                    if(array_key_exists($list[$index]->id, $return_result)){
                        array_push($return_result[$list[$index]->id], $list[$index]);
                    } else {
                        $return_result[$list[$index]->id] = [];
                        array_push($return_result[$list[$index]->id], $list[$index]);
                    }
                }
            }

            return response()->json(['code'=>200, 'lists'=>$return_result, 'query'=> $result['query']], 200);
    }

    public function deleteServiceFromList($id){
        ListService::where('id', $id)->delete();
        return response()->json(['code'=>200, 'message'=>'Deleted successfully'], 200);
    }

    public function startOrder(Request $request){
        $list_id = $request->list_id;
        $orders = $request->orders;

        // set list as started status
        UserList::where('id', $list_id)->update(['is_started' => '1', 'started_at' => date("Y-m-d H:i:s")]);
        
        foreach($orders as $order){
            ListService::where('id', $order['list_service_id'])
                        ->update(
                            [
                                'quantity' => $order['quantity'],
                                'link' => $order['link'],
                                // 'service_type' => $order->service_type,
                                'started_at' => date("Y-m-d H:i:s"),
                                'status' => 1,                                      // pending
                                'inprogress_minute' => 5,                           // cronjob running every 5min
                                // 'completed_minute' => 30
                            ]
                        );
        }
        return response()->json(['code'=>200, 'message'=>'Started successfully'], 200);
    }
}