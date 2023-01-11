<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class OrderHeader extends Model
{
    use HasFactory;
    protected $table = 'order_headers';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'user_id',
        'order_serial_id',
        'service_count',
        'total_cost'
    ];

    public function user() {
        return $this->belongsTo('App\Models\User');
    }

    public function details() {
        return $this->hasMany('App\Models\OrderDetail');
    }


    public static function getHistoryList($user_id){
        $sql = "SELECT `h`.`order_serial_id`, `h`.`total_cost`, `h`.`created_at` AS `order_created_at`, `d`.*, `s`.`provider`, `s`.`service`, `s`.`name`, `s`.`type`, `s`.`rate` AS `current_price`, `s`.`default_currency`, `s`.`min` AS `service_min`, `s`.`max` AS `service_max`
        FROM 
        ( 
            SELECT * FROM `order_headers` WHERE `user_id` = 1
        ) `h`
        LEFT JOIN `order_details` `d` ON `h`.`id` = `d`.`header_id`
        INNER JOIN `services` `s` ON `d`.`service_id` = `s`.`id`
        ORDER BY `header_id` DESC, `id`
        ";

        $result =  DB::select($sql);
       
        return array('result'=>$result, 'query'=>$sql);
    }
}