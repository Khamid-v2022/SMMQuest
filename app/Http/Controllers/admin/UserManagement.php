<?php

namespace App\Http\Controllers\admin;

use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use App\Models\User;

use Illuminate\Support\Facades\Http;

use Illuminate\Support\Facades\Mail;
use App\Mail\Notify;


class UserManagement extends Controller
{
    public function index()
    {
        $pageConfigs = ['myLayout' => 'horizontal'];

        $users = User::get();
        
        return view('content.adminside.user-management', [
            'pageConfigs'=> $pageConfigs, 
            'users' => $users
        ]);
    }

    public function addUser(Request $request) {
        $request->validate([
          'email' => 'required'
        ]);
        
        User::updateOrCreate(['id' => $request->selected_id], [
            'email' => $request->email,
            'is_delete' => $request->is_delete
        ]);

        return response()->json(['code'=>200, 'message'=>'Success'], 200);

    }

    public function deleteUser(Request $request) {
        User::where('id', $request->id)->delete();
        return response()->json(['code'=>200, 'message'=>'Deleted successfully'], 200);
    }

    public function resetPassword(Request $request){
        $user = User::where('id', $request->id)->first();
        
        // send email
        $verify_code = $this->randomString(99);
        $user->verify_code = $verify_code;
        $user->save();

        $active_link = route('reset-password', ['unique_str' => $verify_code]);
        $details = [
            'title' => 'Reset Password',
            'body' => ' Click the link below to reset your password:<br/>' 
                        . '<a href="' . $active_link . '" target="_black">' . $active_link . '</a>'
        ];
        
        try {
            Mail::to($user->email) -> send(new Notify($details));
        } catch (Exception $e) {
            if (count(Mail::failures()) > 0) {
                return response()->json(['code'=>400, 'message'=>'Email could not be sent for some reason. Please try again later.'], 200);
            }
        }
        return response()->json(['code'=>200, 'message'=>'Sent an email', 'user'=>$user], 200);
    }

}
