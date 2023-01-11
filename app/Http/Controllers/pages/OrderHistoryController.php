<?php

namespace App\Http\Controllers\pages;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\UserList;
use App\Models\ListService;
use App\Models\Currency;
use App\Models\Service;
use App\Models\OrderHeader;
use App\Models\OrderDetail;

class OrderHistoryController extends MyController
{
    private $currencies = NULL;

    public function index()
    {
        $pageConfigs = ['myLayout' => 'horizontal'];
        $this->currencies = Currency::where("id", 1)->first();

        return view('content.pages.pages-orders-history', [
            'pageConfigs'=> $pageConfigs
        ]);
    }

    public function loadHistory(Request $request){

        // Using SQL query
        $result = OrderHeader::getHistoryList(Auth::user()->id);
        
        $list = $result['result'];


        $return_result = [];

        if(count($list) > 0){
            if(!$this->currencies){
                // currency table based USD
                $this->currencies = Currency::where("id", 1)->first();
            }

            for($index = 0; $index < count($list); $index++){
                $currency = NULL;
                if(strtoupper($list[$index]->paid_currency) != $request->currency){
                    $currency = $list[$index]->conversion_rate;
                }

                if($currency && $currency != 0) {
                    $list[$index]->paid_price = round(($list[$index]->paid_price / $currency) * $this->currencies[$request->currency], 6);
                }

                // group by list
                if(array_key_exists($list[$index]->header_id . "\0", $return_result)){
                    array_push($return_result[$list[$index]->header_id . "\0"], $list[$index]);
                } else {
                    $return_result[$list[$index]->header_id . "\0"] = [];
                    array_push($return_result[$list[$index]->header_id . "\0"], $list[$index]);
                }
            }
        }

        return response()->json(['code'=>200, 'lists'=>$return_result], 200);
    }

}