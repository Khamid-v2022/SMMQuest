<?php

namespace App\Http\Controllers\pages;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\UserList;
use App\Models\ListService;

class MyListController extends MyController
{
    public function index()
    {
        $pageConfigs = ['myLayout' => 'horizontal'];

        if(Auth::user()->subscrib_status == 0){
            // get my list
            $list = UserList::where('is_started', '0')
                        ->where('user_id', Auth::user()->id)
                        ->with(['services'])
                        ->orderBy('created_at', 'DESC')
                        ->get();
            return view('content.pages.pages-my-list-non-subscrib', [
                'pageConfigs'=> $pageConfigs, 
                'list' => $list
            ]);
        } else {
            $list = UserList::where('is_started', '0')
                        ->where('user_id', Auth::user()->id)
                        ->with(['services'])
                        ->orderBy('created_at', 'DESC')
                        ->get();
            return view('content.pages.pages-my-list-subscrib', [
                'pageConfigs'=> $pageConfigs, 
                'list' => $list
            ]);
        }
    }

    public function startedListPage(){
        if(Auth::user()->subscrib_status == 0){
            $this->index();
            return;
        } 

        $pageConfigs = ['myLayout' => 'horizontal'];
        $started_list = UserList::where('is_started', '1')
                        ->where('user_id', Auth::user()->id)
                        ->with(['services'])
                        ->orderBy('created_at', 'DESC')
                        ->get();
        return view('content.pages.pages-my-started-list', [
            'pageConfigs'=> $pageConfigs, 
            'list' => $started_list
        ]);
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