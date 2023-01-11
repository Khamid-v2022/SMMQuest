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

class MyListController extends MyController
{
    private $currencies = NULL;

    public function index()
    {
        $pageConfigs = ['myLayout' => 'horizontal'];
        $this->currencies = Currency::where("id", 1)->first();

        if(Auth::user()->subscrib_status == 0){
            return view('content.pages.pages-my-list-non-subscrib', [
                'pageConfigs'=> $pageConfigs
            ]);
        } else {

            return view('content.pages.pages-my-list-subscrib', [
                'pageConfigs'=> $pageConfigs
            ]);
        }
    }

    public function loadMyLists(Request $request){
            // get my list by Eloquent -- Do not delete
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
                    if(strtoupper($list[$index]->default_currency) != $request->currency){
                        $currency = $this->currencies[strtoupper($list[$index]->default_currency)];
                    }

                    if($currency && $currency != 0) {
                        $list[$index]->rate = round(($list[$index]->rate / $currency) * $this->currencies[$request->currency], 6);
                        $list[$index]->user_balance = round(($list[$index]->user_balance / $currency) * $this->currencies[$request->currency], 6);
                    }

                    // group by list
                    if(array_key_exists($list[$index]->id . "\0", $return_result)){
                        array_push($return_result[$list[$index]->id . "\0"], $list[$index]);
                    } else {
                        $return_result[$list[$index]->id . "\0"] = [];
                        array_push($return_result[$list[$index]->id . "\0"], $list[$index]);
                    }
                }
            }

            return response()->json(['code'=>200, 'lists'=>$return_result, 'query'=> $result['query']], 200);
    }

    public function deleteServiceFromList($id){
        ListService::where('id', $id)->delete();
        return response()->json(['code'=>200, 'message'=>'Deleted successfully'], 200);
    }

    // public function startOrder(Request $request){
    //     $list_id = $request->list_id;
    //     $orders = $request->orders;

    //     // set list as started status
    //     UserList::where('id', $list_id)->update(['is_started' => '1', 'started_at' => date("Y-m-d H:i:s")]);
        
    //     foreach($orders as $order){
    //         ListService::where('id', $order['list_service_id'])
    //                     ->update(
    //                         [
    //                             'quantity' => $order['quantity'],
    //                             'link' => $order['link'],
    //                             // 'service_type' => $order->service_type,
    //                             'started_at' => date("Y-m-d H:i:s"),
    //                             'status' => 1,                                      // pending
    //                             'inprogress_minute' => 5,                           // cronjob running every 5min
    //                             // 'completed_minute' => 30
    //                         ]
    //                     );
    //     }
    //     return response()->json(['code'=>200, 'message'=>'Started successfully'], 200);
    // }

    public function startTestOrder(Request $request) {
        $max_order_id = OrderHeader::where('user_id', Auth::user()->id)->max('order_serial_id');

        $header = OrderHeader::create([
            'user_id' => Auth::user()->id,
            'order_serial_id' => $max_order_id + 1,
            'service_count' => $request->service_count,
            'total_cost' => $request->total_cost
        ]);

        $list = json_decode($request->order_list);
        
        if(!$this->currencies){
            // currency table based USD
            $this->currencies = Currency::where("id", 1)->first();
        }

        foreach($list as $item){
            // get service info for get price & currency to pay
            $service_info = Service::where('id', $item->service_id)->first();

            OrderDetail::create([
                'header_id'     => $header->id,
                'list_id'       => $item->list_id,
                'service_id'    => $item->service_id,
                'paid_price'    => $service_info->rate,
                'paid_currency' => $service_info->default_currency,
                'converstion_rate' => $this->currencies[strtoupper($service_info->default_currency)],

                'cost'          => $item->cost,
                'quantity'      => $item->quentity,
                'link'          => $item->link,
                'comments'      => $item->comments,
                'usernames'     => $item->usernames,
                'username'      => $item->username,
                'hashtags'      => $item->hashtags,
                'hashtag'       => $item->hashtag,
                'media'         => $item->media,
                'answer_number' => $item->answer_number,
                'groups'        => $item->groups,
                'min'           => $item->min,
                'max'           => $item->max,
                'delay'         => $item->delay,
                'start_count'   => 0,
                'remains'       => $item->quentity,
                'in_progress_minute' => 5,
                'completed_minute'   => 60,
                'status'      => 1
            ]);
        }
          
        return response()->json(['code'=>200, 'message'=>'Started successfully'], 200);
    }
}