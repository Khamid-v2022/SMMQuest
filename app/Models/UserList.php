<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class UserList extends Model
{
    use HasFactory;
    protected $table = 'user_lists';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'user_id',
        'list_name',
        'is_started',
        'started_at',
    ];

    public function user() {
        return $this->belongsTo('App\Models\User');
    }

    public function services() {
        return $this->hasMany('App\Models\ListService', 'list_id');
    }

    public function order_details(){
        return $this->hasMany('App\Models\OrderDetail', 'list_id');
    }

    public static function getMyLists($user_id){
        $sql = "SELECT `ul`.*, `ls`.`provider_id`, `ls`.`api_template`, `up`.`is_favorite`, `up`.`user_balance`,  `s`.`default_currency`, `ls`.`id` AS `list_service_id`, `s`.`provider`, `s`.`id` AS `service_id`, `s`.`service`, `s`.`name`, `s`.`rate`, `s`.`min`, `s`.`max`, `s`.`type`
        FROM
        (SELECT * FROM `user_lists` WHERE `user_id` = " . $user_id . ") `ul`
        LEFT JOIN `list_services` `ls` ON `ul`.`id` = `ls`.`list_id`
        LEFT JOIN `services` `s` ON `ls`.`service_id` = `s`.`id`
        LEFT JOIN `user_provider` `up` ON `ls`.`provider_id` = `up`.`provider_id` AND `up`.`user_id` = " . $user_id . "
        ORDER BY `id` DESC
        ";

        $result =  DB::select($sql);
       
        return array('result'=>$result, 'query'=>$sql);
    }
}
